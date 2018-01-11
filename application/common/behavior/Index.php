<?php
/**
 * Created by PhpStorm.
 * User: 王崇全
 * Date: 2017/12/16
 * Time: 10:01
 */

namespace app\common\behavior;

use think\facade\Request;

class Index
{

    public function moduleInit() {

        define("MODULE", Request::module());
        define("CONTROLLER", Request::controller());
        define("ACTION", Request::action());
        define("URL_PATH", MODULE."/".CONTROLLER."/".ACTION);
    }

}