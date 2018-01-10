<?php
/**
 * Created by PhpStorm.
 * User: Chongquan
 * Time: 2018-01-10 20:03
 */

namespace app\workman\controller;

use Workerman\Connection\AsyncTcpConnection;
use Workerman\Worker;

class TcpController
{

	public function __construct()
	{

		$flash_policy = new Worker('tcp://0.0.0.0:8934');

		$flash_policy->onMessage = function ($connection, $message) {

			$ws_connection = new AsyncTcpConnection("ws://127.0.0.1:8933");

			$ws_connection->onConnect = function ($connection) use ($message) {
				$connection->send($message);
			};

			// 远程websocket服务器发来消息时
			$ws_connection->onMessage = function ($connection, $data) {
				echo "recv: $data\n";
				$connection->close();
			};
			// 连接上发生错误时，一般是连接远程websocket服务器失败错误
			$ws_connection->onError = function ($connection, $code, $msg) {
				echo "error: $msg\n";
			};
			// 当连接远程websocket服务器的连接断开时
			$ws_connection->onClose = function ($connection) {
				echo "connection closed\n";
			};
			// 设置好以上各种回调后，执行连接操作
			$ws_connection->connect();

			$connection->send("you said :$message\r\n".$connection->getRemoteIp().":".$connection->getRemotePort()."\r\n");
		};

		Worker::runAll();
	}
}