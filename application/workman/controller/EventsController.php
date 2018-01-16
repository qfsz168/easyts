<?php
/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */

//declare(ticks=1);
use app\workman\controller\SocketController;
use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class EventsController
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        // 向当前client_id发送数据
        Gateway::sendToClient($client_id, json_encode([
            'type' => "connect",
            'data' => ["client_id" => $client_id],
        ]));
    }

    /**
     * 当客户端发来消息时触发
     *
     * @param int   $client_id 连接id
     * @param mixed $message   具体消息
     */
    public static function onMessage($client_id, $message) {
        $message = trim($message, "\n");
        $data    = json_decode($message, true);
        try {
            if (isset($data["type"]) && $data["type"] == SocketController::DATA_TYPE_NAME_TCP) {
                db("data",[],false)->insert([
                    "raw"         => $message,
                    "client_id"   => $client_id,
                    "sn"          => $data["sn"],
                    "create_time" => date("Y-m-d H:i:s"),
                ]);

                Gateway::sendToAll(date("H:i:s")." : {$data["data"]}");
            }
        } catch (\Exception $e) {
            echo $e->getMessage()."\n";
        }

    }

    /**
     * 当用户断开连接时触发
     *
     * @param int $client_id 连接id
     */
    public static function onClose($client_id) {
    }

}