<?php
/**
 * Created by PhpStorm.
 * User: sdjs-014
 * Date: 2017/8/20
 * Time: 11:59
 */

namespace app\index\controller;

use app\adm\model\Config;
use app\common\model\Group;
use think\facade\Cache;
use think\Request;

class BaseController extends \app\common\controller\BaseController
{
    const CACHE_TIME = 60;

    public function __construct(Request $request = null) {
        parent::__construct();

        $ci = Cache::remember("config_data", function ()
        {
            $c = new Config();

            return $c->getValues([
                "site_name",
                "address",
                "tel",
                "fax",
                "abstract",
                "record",
                "email",
                "copyright",
                "web_discription",
                "web_keyword",
            ]);
        }, self::CACHE_TIME);

        //title
        $this->assign("siteName", $ci["site_name"] ?? null);
        //地址
        $this->assign('address', $ci["address"] ?? null);
        //电话
        $this->assign('tel', $ci["tel"] ?? null);
        //传真
        $this->assign('fax', $ci["fax"] ?? null);
        //简介
        $this->assign('abstract', $ci["abstract"] ?? null);
        //备案号
        $this->assign('record', $ci["record"] ?? null);
        //邮箱
        $this->assign('email', $ci["email"] ?? null);

        $this->assign('webDiscription', $ci["web_discription"] ?? null);
        $this->assign('webKeyword', $ci["web_keyword"] ?? null);
        $this->assign('copyright', $ci["copyright"] ?? null);

        //导航栏
        try {

            $article = Cache::remember("nav_data1", function ()
            {
                $g = new Group();

                return $g->GetList("article", "", false);
            }, self::CACHE_TIME);

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->assign("navs", $article);
    }

}