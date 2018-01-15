<?php

namespace app\index\controller;

class IndexController extends BaseController
{
    public function _empty() {
        return jsonp(["a" => 1]);
    }

    public function index() {

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
