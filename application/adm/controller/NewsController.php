<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-12-15
 * Time: 14:04
 */

namespace app\adm\controller;

use app\common\model\Article;
use think\Request;
use app\common\model\Group;
use think\facade\Session;

class NewsController extends BaseController
{
    public function __construct(Request $request = null) {
        parent::__construct($request);

        $this->assign("activeNavNo", 2);
    }

    public function add() {
        $this->assign("webTitle", "文章添加");

        $g = new Group;

        try {
            $list = $g->GetList("article", null, false);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->assign('glist', $list);

        return $this->fetch();
    }

    public function edit() {
        $this->assign("webTitle", "文章修改");

        $this->I([
            [
                "id|文章ID",
                null,
                "s",
                "require",
            ],
        ]);

        $a = new Article();
        $g = new Group;

        try {
            $ai = $a->getInfo(self::$_i["id"]);

            $pgid = $g->getParentGroupID($ai["group_id"]);
            if ($pgid) {
                $ai["cg1"] = $pgid;
                $ai["cg2"] = $ai["group_id"];
            } else {
                $ai["cg1"] = $ai["group_id"];
                $ai["cg2"] = "";
            }
            unset($ai["group_id"]);

            $list = $g->GetList("article", null, false);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->assign('ai', $ai);
        $this->assign('glist', $list);

        return $this->fetch();
    }

    public function apiAdd() {

        $this->I([
            [
                "title|文章标题",
                null,
                "s",
                "require",
            ],
            [
                "cg1|一级栏目",
                null,
                "s",
                "require",
            ],
            [
                "cg2|二级栏目",
                null,
                "s",
            ],
            [
                "content|文章内容",
                null,
                "s",
                "require",
            ],
        ]);
        if (!self::$_i["cg2"]) {
            self::$_i["cg2"] = null;
        }

        $coverFile = Session::get("articleCoverFile");
        Session::delete("articleCoverFile");
        if (!$coverFile) {
            $this->error("请先上传封面");
        }

        $art = new Article();

        try {
            $art->Add(self::$_i["title"], self::$_i["cg2"] ?? self::$_i["cg1"], self::$_i["content"], $coverFile);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('添加成功');
    }

    public function apiEdit() {

        $this->I([
            [
                "id|文章ID",
                null,
                "s",
                "require",
            ],
            [
                "title|文章标题",
                null,
                "s",
            ],
            [
                "cg1|一级栏目",
                null,
                "s",
            ],
            [
                "cg2|二级栏目",
                null,
                "s",
            ],
            [
                "content|文章内容",
                null,
                "s",
            ],
        ]);
        if (!self::$_i["cg2"]) {
            self::$_i["cg2"] = null;
        }
        if (!self::$_i["title"]) {
            self::$_i["title"] = null;
        }
        if (!self::$_i["cg1"]) {
            self::$_i["cg1"] = null;
        }
        if (!self::$_i["content"]) {
            self::$_i["content"] = null;
        }

        $coverFile = Session::get("articleCoverFile");
        Session::delete("articleCoverFile");
        if (!$coverFile) {
            $coverFile = null;
        }

        $art = new Article();

        try {
            $art->edit(self::$_i["id"], self::$_i["title"], self::$_i["cg2"] ?? self::$_i["cg1"], self::$_i["content"], $coverFile);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('修改成功');
    }

    public function index() {
        $this->assign("webTitle", "文章管理");

        $g = new Group;

        try {
            $list = $g->GetList("article", null, false);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->assign('glist', $list);

        return $this->fetch();

    }

    public function apiGetCG2() {

        $this->I([
            [
                "id|栏目ID",
                null,
                "s",
            ],
        ]);

        if (!self::$_i["id"]) {
            $this->success("", null, []);
        }

        $g = new Group();

        try {
            $list = $g->GetList("article", self::$_i["id"]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("", null, $list);
    }

    //api-列表
    public function apiGetList() {
        $this->I([
            [
                "pageNo",
                null,
                "d",
            ],
            [
                "pageSize",
                null,
                "d",
            ],
            [
                "gid",
                null,
                "s",
            ],
            [
                "startTime",
                null,
                "s",
            ],
            [
                "endTime",
                null,
                "s",
            ],
        ]);

        if (!self::$_i["startTime"]) {
            self::$_i["startTime"] = null;
        }
        if (!self::$_i["endTime"]) {
            self::$_i["endTime"] = null;
        }
        if (!self::$_i["gid"]) {
            self::$_i["gid"] = null;
        }

        $a    = new Article();
        $list = [
            [],
            0,
        ];
        try {
            $list = $a->getList(self::$_i["pageNo"], self::$_i["pageSize"], self::$_i["gid"], self::$_i["startTime"], self::$_i["endTime"]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }


        $this->success("请求成功", null, [
            "list"  => $list[0],
            "count" => $list[1],
        ]);
    }

    //api-删除
    public function apiDel() {
        $this->I([
            [
                "id|文章ID",
                null,
                "s",
                "require",
            ],
        ]);

        $a = new Article();
        try {
            $a->del(self::$_i["id"]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("删除成功");
    }

    //api-上传封面
    public function apiUplImage() {

        $file = request()->file('file');

        $path = ROOT_PATH."public".DS."upload".DS."image";
        mk_dir($path);

        $info = $file->move($path);
        if (!$info) {
            $this->error("上传失败:".$file->getError());
        }

        $file = "/upload/image/".date("Ymd")."/".$info->getFilename();


        //保存文件名
        Session::set("articleCoverFile", $file);

        $this->success("上传成功");
    }

}