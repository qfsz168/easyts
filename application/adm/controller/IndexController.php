<?php
namespace app\adm\controller;

use think\Request;

class IndexController extends BaseController
{

    public function __construct(Request $request = null) {
        parent::__construct($request);

        $this->assign("activeNavNo", 0);
    }

    public function index() {

        $this->assign("webTitle", "首页");


        return $this->fetch();
    }

}
