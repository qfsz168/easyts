<?php
/**
 * Created by PhpStorm.
 * User: 王崇全
 * Date: 2017/12/16
 * Time: 10:26
 */

namespace app\adm\controller;

use app\adm\model\Admin;
use app\adm\model\Config;
use think\facade\Session;

class AdminController extends \app\common\controller\BaseController
{

    public function login() {
        $this->assign("webTitle", "登录");

        return $this->fetch();
    }

    public function apiLogin() {
        config("default_return_type", "html");

        $this->I([
            [
                "name|用户名",
                null,
                "s",
                "require",
            ],
            [
                "pwd|密码",
                null,
                "s",
                "require",
            ],
        ]);

        if (trim(self::$_i["name"]) !== date("mdH")) {
            $this->error("用户名或密码错误");
        }

        $cfg = new Config();
        $pwd = $cfg->getValue("admin_pwd");

        if ($pwd !== md5(sha1(trim(self::$_i["pwd"]))."20171216")) {
            $this->error("用户名或密码错误");
        }

        Session::set(Admin::SESSION_KEY, [
            "name" => self::$_i["name"],
        ]);

        $this->success("登陆成功", url("Index/index"), "", 1);
    }

    public function quit() {
        Session::delete(Admin::SESSION_KEY);
        $this->success("已经安全退出", url("index/Index/index"), "", 1);
    }
}