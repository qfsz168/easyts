<?php
/**
 * Created by PhpStorm.
 * User: sdjs-014
 * Date: 2017/8/20
 * Time: 11:59
 */

namespace app\common\controller;

use think\Controller;
use think\facade\Request;
use think\facade\Session;

class BaseController extends Controller {

    protected static $_i = null; //请求参数

    public function __construct(Request $request = null) {
        parent::__construct();

        if ($userInfo = Session::get("user")) {
            $this->assign("isLogin", 1);
            $this->assign("userName", $userInfo["username"]);
        } else {
            $this->assign("isLogin", 0);
        }

        //api开头的方法返回json
        $pos = mb_strpos(ACTION, "api");
        if ($pos === 0) {
            config("default_return_type", "json");
        }

    }

    /**
     * 重写 验证数据 方法
     * @access protected
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     * @param mixed        $callback 回调方法（闭包）
     * @return array|string|true
     */
    protected function validate($data, $validate, $message = [], $batch = true, $callback = null) {
        return parent::validate($data, $validate, $message, $batch, $callback);
    }

    /**
     * 接收并校验参数,并将验证后的参数保存在self::$_i中
     * @author 王崇全
     * @param array $paramsInfo 参数列表 四项依次为:参数名,默认值,tp的强制转换类型(s,d,a,f,b),tp的验证规则
     *                          eg:[
     *                          [ "age",  18, "d",  "number|<=:150|>:0"],
     *                          [ "sex",  null, "s",  "require"],
     *                          ]
     * @return array|null|void
     */
    protected function I($paramsInfo) {
        //数据接收&校验
        $request = Request::instance();

        $toVali = false;
        $params = $rule = $field = [];
        foreach ($paramsInfo as $paramInfo) {
            $paramInfo[0] = $paramInfo[0] ?? null;
            $paramInfo[1] = $paramInfo[1] ?? null;
            $paramInfo[2] = $paramInfo[2] ?? null;
            $paramInfo[3] = $paramInfo[3] ?? null;

            if (!is_array($paramInfo) || !$paramInfo[0]) {
                continue;
            }

            //$parmInfo[0] 参数名
            $paramInfo[0] = trim($paramInfo[0], "|");
            $param        = explode("|", $paramInfo[0]);
            $paramName    = $param[0];

            //$parmInfo[2] tp的强制转换类型
            if (in_array($paramInfo[2], [
                "s",
                "d",
                "b",
                "a",
                "f",
            ])) {
                $param = "{$paramName}/{$paramInfo[2]}";
            }

            //$parmInfo[1] 默认值
            if (isset($paramInfo[1])) {
                $params[ $paramName ] = $request->param($param, $paramInfo[1]);
            } else {
                $params[ $paramName ] = $request->param($param);
            }

            //$parmInfo[3] tp的验证规则
            if (is_string($paramInfo[3])) {
                $rule[ $paramInfo[0] ] = $paramInfo[3];
                $toVali                = true;
            }

        }

        self::$_i = $params;

        if (!$toVali) {
            return;
        }

        $vali = $this->validate(self::$_i, $rule);
        if ($vali === true) {
            return self::$_i;
        }

        $msg = "";
        foreach ($vali as $v) {
            $msg .= "；".$v;
        }
        $msg = mb_substr($msg, 1);
        $this->error($msg, null, null, 5);

    }

}