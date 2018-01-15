<?php
/**
 * Created by PhpStorm.
 * User: 王崇全
 * Date: 2018/1/15
 * Time: 14:31
 */

namespace app\index\controller;


use think\facade\Request;

class ErrorController
{
    public function _empty() {
        return json(["a" => 1]);
    }

    public function index(Request $request) {
        return json(["a" => 1]);
    }

}