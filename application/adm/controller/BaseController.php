<?php
/**
 * Created by PhpStorm.
 * User: sdjs-014
 * Date: 2017/8/20
 * Time: 11:59
 */

namespace app\adm\controller;

use app\adm\model\Admin;
use think\Request;

class BaseController extends \app\common\controller\BaseController
{

    public function __construct(Request $request = null) {
        parent::__construct();

        $ai = Admin::checkLogin();
        if (false === $ai) {
            $this->error("请登录后再进行操作", url("Admin/login"));
        }

    }

}