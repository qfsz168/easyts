<?php
/**
 * Created by PhpStorm.
 * User: 王崇全
 * Date: 2017/12/16
 * Time: 10:01
 */

namespace app\common\behavior;

use think\Request;

class Index
{

    public function moduleInit()
    {
        $request=new Request();
        define("MODULE", $request->module());
        define("CONTROLLER", $request->controller());
        define("ACTION", $request->action());
        define("URL_PATH", MODULE."/".CONTROLLER."/".ACTION);
    }

}