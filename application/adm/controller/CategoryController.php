<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-12-15
 * Time: 10:51
 */
namespace app\adm\controller;

use app\common\model\GroupRole;
use think\Request;
use app\common\model\Group;

class CategoryController extends BaseController
{
    public function __construct(Request $request = null) {
        parent::__construct($request);

        $this->assign("activeNavNo", 1);
    }

    //栏目列表
    public function index() {
        $this->assign("webTitle", "栏目管理");

        $g = new Group;

        try {
            $list = $g->GetList("article", null, false);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->assign('glist', $list);

        return $this->fetch();
    }

    //栏目添加-页面
    public function add() {
        $this->assign("webTitle", "栏目添加");

        $group = new Group();

        try {
            $list = $group->GetList('article');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->assign('list', $list);

        return $this->fetch();
    }

    //栏目添加-逻辑
    public function apiAdd() {
        config("default_return_type", "html");

        $this->I([
            [
                "cateName|栏目名称",
                null,
                "s",
                "require",
            ],
            [
                "pid|父级栏目",
                null,
                "s",
            ],
        ]);

        $group = new Group();

        try {
            $group->Add('article', self::$_i["cateName"], self::$_i["pid"]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('添加成功！');
    }

    public function apiDel() {
        $this->I([
            [
                "id|栏目编号",
                null,
                "s",
                "require",
            ],
        ]);

        $g = new Group;

        try {
            $g->Del(self::$_i["id"]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("删除成功");
    }

    public function apiEdit() {
        $this->I([
            [
                "id|栏目编号",
                null,
                "s",
                "require",
            ],
            [
                "title|栏目名称",
                null,
                "s",
                "require",
            ],
        ]);

        $g = new Group;

        try {
            $g->Edit(self::$_i["id"], self::$_i["title"]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("修改成功");
    }

    public function apiMoveTo() {
        $this->I([
            [
                "gid",
                null,
                "s",
                "require",
            ],
            [
                "pos",
                null,
                "s",
                "require",
            ],
        ]);

        $g = new Group;

        try {
            $g->MoveTo(self::$_i["gid"], self::$_i["pos"]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("修改成功");
    }

    public function apiSetNotice() {
        $in = $this->I([
            [
                "id|栏目编号",
                null,
                "s",
                "require",
            ],
        ]);

        $gr = new GroupRole();

        try {
            $gr->setRole($in["id"], "notice");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("操作成功");
    }

    public function apiSetNews() {
        $in = $this->I([
            [
                "id|栏目编号",
                null,
                "s",
                "require",
            ],
        ]);

        $gr = new GroupRole();

        try {
            $gr->setRole($in["id"], "news");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("操作成功");
    }

}