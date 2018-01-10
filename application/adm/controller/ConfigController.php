<?php

namespace app\adm\controller;

use app\adm\model\Config;
use think\Request;

class ConfigController extends BaseController
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);

		$this->assign("activeNavNo", 3);
	}

	public function index()
	{
		$this->assign("webTitle", "网站设置");

		$c = new Config();

		try
		{
			$list = $c->getList();
		}
		catch (\Exception $e)
		{
			$this->error($e->getMessage());
		}

		$this->assign('glist', $list);

		return $this->fetch();
	}


	//api-
	public function apiEdit()
	{
		$this->I([
			[
				"key",
				null,
				"s",
				"require",
			],
			[
				"value",
				null,
				"s",
				"require",
			],
		]);

		$a = new Config();
		try
		{
			$a->setValue(trim(self::$_i["key"]), trim(self::$_i["value"]));
		}
		catch (\Exception $e)
		{
			$this->error($e->getMessage());
		}

		$this->success("修改成功");
	}

	//api-
	public function apiSetSy()
	{
		$this->I([
			[
				"id|文章ID",
				null,
				"s",
				"require",
			],
		]);

		$a = new Config();
		try
		{
			$a->setValue("syaid", trim(self::$_i["id"]));
		}
		catch (\Exception $e)
		{
			$this->error($e->getMessage());
		}

		$this->success("修改成功");
	}


}
