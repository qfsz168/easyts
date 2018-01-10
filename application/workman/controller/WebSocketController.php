<?php
/**
 * Created by PhpStorm.
 * User: Chongquan
 * Time: 2018-01-10 20:04
 */

namespace app\workman\controller;

use Workerman\Worker;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;

/**
 * 名称 WebSocketController
 * 功能
 * @package app\workman\controller
 */
class WebSocketController
{
	const WS_PORT = 8933;

	public function __construct()
	{
		//【1】register 服务必须是text协议
		$register = new Register('text://0.0.0.0:1236');

		//【2】 bussinessWorker 进程
		$worker                  = new BusinessWorker();
		$worker->name            = 'qfsz168_worker'; // worker名称
		$worker->count           = 4; // bussinessWorker进程数量
		$worker->eventHandler    = "EventsController"; //事件处理类，默认是 Event 类
		$worker->registerAddress = '127.0.0.1:1236'; // 服务注册地址

		//【3】gateway 进程
		$gateway        = new Gateway("websocket://0.0.0.0:".self::WS_PORT);
		$gateway->name  = 'qfsz168_gateway'; // gateway名称，status方便查看
		$gateway->count = 4; // gateway进程数
		$gateway->lanIp = "0.0.0.0"; // 本机ip，分布式部署时使用内网ip

		// 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
		// 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
		$gateway->startPort = 4000;

		$gateway->registerAddress = '127.0.0.1:1236'; // 服务注册地址

		$gateway->pingInterval         = 20; // 心跳间隔
		$gateway->pingNotResponseLimit = 0;
		$gateway->pingData             = ''; // 心跳数据

		// 当客户端连接上来时，设置连接的onWebSocketConnect，即在websocket握手时的回调
		$gateway->onConnect = function ($connection) {
			$connection->onWebSocketConnect = function ($connection, $http_header) {
				// 可以在这里判断连接来源是否合法，不合法就关掉连接
				// $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接
				if (false && $_SERVER['HTTP_ORIGIN'] != "192.168.1.3:8013")
				{
					$connection->close();
					var_dump("refused:".$_SERVER['HTTP_ORIGIN']);
				}
				// onWebSocketConnect 里面$_GET $_SERVER是可用的
				// var_dump($_GET, $_SERVER);
			};
		};

		Worker::runAll();
	}

}