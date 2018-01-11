<?php

namespace app\index\controller;

class IndexController extends BaseController
{
	public function index()
	{
		return $this->fetch();
	}

	public function apiTest()
	{
		$i = $this->I([
			[
				"name|姓名",
				null,
				"s",
				"require",
			],
		]);

		$this->success("",null,$i);
	}
}
