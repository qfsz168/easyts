<?php
namespace app\index\controller;

class IndexController extends BaseController
{
    public function index() {
        return $this->fetch();
    }
}
