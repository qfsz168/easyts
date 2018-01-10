<?php
/**
 * Created by PhpStorm.
 * User: Chongquan
 * Time: 2018-01-10 20:03
 */

namespace app\workman\controller;

use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class FlashSocketController
{
	protected $worker;
	protected $socket    = 'tcp://0.0.0.0:843';
	protected $protocol  = 'http';
	protected $host      = '0.0.0.0';
	protected $port      = '2346';
	protected $processes = 4;

	/**
	 * 架构函数
	 * @access public
	 */
	public function __construct()
	{
		// 实例化 Websocket 服务
		$this->worker = new Worker($this->socket ?: $this->protocol.'://'.$this->host.':'.$this->port);
		// 设置进程数
		$this->worker->count = $this->processes;
		// 初始化
		$this->init();

		// 设置回调
		foreach (
			[
				'onWorkerStart',
				'onConnect',
				'onMessage',
				'onClose',
				'onError',
				'onBufferFull',
				'onBufferDrain',
				'onWorkerStop',
				'onWorkerReload',
			] as $event
		)
		{
			if (method_exists($this, $event))
			{
				$this->worker->$event = [
					$this,
					$event,
				];
			}
		}
		// Run worker
		Worker::runAll();
	}

	/**
	 * 收到信息
	 * @param $connection
	 * @param $data
	 */
	public function onMessage(TcpConnection $connection, $data)
	{
		//		policy-file-request
		//		因为flash总是先会从843找，如果找不到再从websocket端口
		$connection->send('<?xml version="1.0"?><cross-domain-policy><site-control permitted-cross-domain-policies="all"/><allow-access-from domain="*" to-ports="*"/></cross-domain-policy>'."\0");
	}

	/**
	 * 当连接建立时触发的回调函数
	 * @param $connection
	 */
	public function onConnect($connection)
	{
	}

	/**
	 * 当连接断开时触发的回调函数
	 * @param $connection
	 */
	public function onClose($connection)
	{
	}

	/**
	 * 当客户端的连接上发生错误时触发
	 * @param $connection
	 * @param $code
	 * @param $msg
	 */
	public function onError($connection, $code, $msg)
	{
		echo "error $code $msg\n";
	}

	/**
	 * 每个进程启动
	 * @param $worker
	 */
	public function onWorkerStart($worker)
	{
	}

	protected function init()
	{
	}
}