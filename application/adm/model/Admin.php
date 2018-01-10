<?php
/**
 * Created by PhpStorm.
 * User: 王崇全
 * Date: 2017/12/16
 * Time: 11:48
 */
namespace app\adm\model;

use think\facade\Session;

class Admin
{

    const SESSION_KEY = "adminInfo";

    /**
     * checkLogin
     * @author 王崇全
     * @date
     * @return bool|mixed
     */
    public static function checkLogin() {
        $ai = Session::get(self::SESSION_KEY);
        if (!$ai) {
            return false;
        }

        return $ai;
    }

}