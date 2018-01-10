<?php

namespace app\index\controller;

use app\common\model\Article;
use app\common\model\Group;

class ArticleController extends BaseController
{
    public function index() {
        $this->I([
            [
                "gid|栏目ID",
                null,
                "s",
                "require",
            ],
        ]);

        $g = new Group();
        $a = new Article();
        try {
            $count  = $a->getCount(self::$_i["gid"]);
            $gTitle = $g->Gettitle(self::$_i["gid"]);
            $pgid   = $g->getParentGroupID(self::$_i["gid"]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        if ($count == 1) {
            $id = $a->getFirst(self::$_i["gid"]);
            $this->redirect(url("content", ["id" => $id]));
        }

        $this->assign("webTitle", $gTitle);

        $this->assign("gid", self::$_i["gid"]);
        $this->assign("pgid", $pgid);

        return $this->fetch();
    }

    public function content() {
        $this->I([
            [
                "id|文章ID",
                null,
                "s",
                "require",
            ],
        ]);

        $a = new Article();
        $g = new Group();

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

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->assign("webTitle", $ai["title"]);
        $this->assign('ai', $ai);
        $this->assign("pgid", $ai["cg1"]);

        return $this->fetch();
    }

    //api-列表
    public function apiList() {
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
        ]);
        if (!self::$_i["gid"]) {
            self::$_i["gid"] = null;
        }

        $a    = new Article();
        $list = [
            [],
            0,
        ];
        try {
            $list = $a->getList(self::$_i["pageNo"], self::$_i["pageSize"], self::$_i["gid"]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("请求成功", null, [
            "list"  => $list[0],
            "count" => $list[1],
        ]);
    }

    public function getCover() {
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
            $file = $a->getCover(self::$_i["id"]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success("", null, $file);
    }

}
