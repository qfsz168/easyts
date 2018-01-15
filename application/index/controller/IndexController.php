<?php

namespace app\index\controller;

class IndexController extends BaseController
{
    public function _empty() {
        return jsonp(["a" => 1]);
    }

    public function index() {
        halt(input("a", 111, "aa"));
        widget("index", ["a" => 2]);

        return view();
    }

    public function apiTest() {
        $i = $this->I([
            [
                "name|姓名",
                null,
                "s",
                "require",
            ],
        ]);

        $this->success("", null, $i);
    }
}
