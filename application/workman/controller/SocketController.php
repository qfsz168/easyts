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
class SocketController
{
	const WS_PORT                  = 8933;
	const TCP_PORT                 = 8934;
	const REGISTER_PORT            = 1236;
	const TCP2WS_PORT              = 7273;
	const TCP2WS_INNER_PORT_BEGIN  = 2800;
	const GATEWAY_INNER_PORT_BEGIN = 4000;

	public function __construct()
	{
		//【1.1】register 服务必须是text协议
		$register       = new Register('text://127.0.0.1:'.self::REGISTER_PORT);
		$register->name = "gateway_register";

		//【1.2】 bussinessWorker 进程
		$worker                  = new BusinessWorker();
		$worker->name            = 'gateway_worker'; // worker名称
		$worker->count           = 4; // bussinessWorker进程数量
		$worker->eventHandler    = "EventsController"; //事件处理类，默认是 Event 类
		$worker->registerAddress = '127.0.0.1:'.self::REGISTER_PORT; // 服务注册地址

		//【1.3】websocket_server 进程
		$gateway        = new Gateway("websocket://0.0.0.0:".self::WS_PORT);
		$gateway->name  = 'websocket_server'; // gateway名称，status方便查看
		$gateway->count = 4; // gateway进程数
		$gateway->lanIp = "127.0.0.1"; // 本机ip，分布式部署时使用内网ip

		// 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
		// 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
		$gateway->startPort = self::GATEWAY_INNER_PORT_BEGIN;

		$gateway->registerAddress = '127.0.0.1:'.self::REGISTER_PORT; // 服务注册地址

		$gateway->pingInterval         = 20; // 心跳间隔
		$gateway->pingNotResponseLimit = 0;
		$gateway->pingData             = ''; // 心跳数据

		// 当客户端连接上来时，设置连接的onWebSocketConnect，即在websocket握手时的回调
		$gateway->onConnect = function ($connection) {
			$connection->onWebSocketConnect = function ($connection, $http_header) {
				// 可以在这里判断连接来源是否合法，不合法就关掉连接
				// $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接
				//				if (false && $_SERVER['HTTP_ORIGIN'] != "192.168.1.3:8013")
				//				{
				//					$connection->close();
				//					var_dump("refused:".$_SERVER['HTTP_ORIGIN']);
				//				}
				// onWebSocketConnect 里面$_GET $_SERVER是可用的
				// var_dump($_GET, $_SERVER);
			};
		};

		//【2】 内部推送端口 tcp->ws
		$internal_gateway            = new Gateway("Text://127.0.0.1:".self::TCP2WS_PORT);
		$internal_gateway->name      = 'tcp->ws';
		$internal_gateway->count     = 2;
		$internal_gateway->startPort = self::TCP2WS_INNER_PORT_BEGIN;
		// register 服务监听的端口，默认是1236
		$internal_gateway->registerAddress = '127.0.0.1:'.self::REGISTER_PORT;

		//【3】Tcp server 进程
		$flash_policy            = new Worker('tcp://0.0.0.0:'.self::TCP_PORT);
		$flash_policy->name      = "tcp_server";
		$flash_policy->count     = 4;
		$flash_policy->onMessage = function ($connection, $message) {

			// 建立连接，@see http://php.net/manual/zh/function.stream-socket-client.php
			$client = stream_socket_client('tcp://127.0.0.1:'.self::TCP2WS_PORT);
			if (!$client)
			{
				echo("can not connect");
			}
			// 模拟超级用户，以文本协议发送数据，注意Text文本协议末尾有换行符（发送的数据中最好有能识别超级用户的字段），这样在Event.php中的onMessage方法中便能收到这个数据，然后做相应的处理即可
			fwrite($client, $message."\n");

			//$connection->send("you said :$message\r\n".$connection->getRemoteIp().":".$connection->getRemotePort()."\r\n");
		};

		//【4】flashSocket 授权 进程
		$flash_policy            = new Worker('tcp://0.0.0.0:843');
		$flash_policy->name      = "flash_policy";
		$flash_policy->onMessage = function ($connection, $message) {
			$connection->send('<?xml version="1.0"?><cross-domain-policy><site-control permitted-cross-domain-policies="all"/><allow-access-from domain="*" to-ports="*"/></cross-domain-policy>'."\0");
		};

		Worker::runAll();
	}

}