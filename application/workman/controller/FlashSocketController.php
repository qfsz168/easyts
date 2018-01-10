<?php
/**
 * Created by PhpStorm.
 * User: Chongquan
 * Time: 2018-01-10 20:03
 */

namespace app\workman\controller;

use Workerman\Worker;

class FlashSocketController
{

	public function __construct()
	{

		$flash_policy = new Worker('tcp://0.0.0.0:843');

		$flash_policy->onMessage = function ($connection, $message) {
			$connection->send('<?xml version="1.0"?><cross-domain-policy><site-control permitted-cross-domain-policies="all"/><allow-access-from domain="*" to-ports="*"/></cross-domain-policy>'."\0");
		};

		Worker::runAll();
	}
}