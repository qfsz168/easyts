<?php

//创建Server对象，监听 127.0.0.1:9501端口
$serv = new swoole_server("0.0.0.0", 9501);

//监听连接进入事件
$serv->on('connect', function ($serv, $fd)
{
    $serv->send($fd, "1");
});

//监听数据发送事件
$serv->on('receive', function ($serv, $fd, $from_id, $data)
{

    $cli = new swoole_http_client('127.0.0.1', 8282);

    $cli->on('message', function ($_cli, $frame)
    {
    });

    $cli->upgrade('/', function ($cli)
    {
    });

    $cli->push($data);

    $swoole_mysql = new swoole_mysql;
    $server       = [
        'host'     => 'localhost',
        'port'     => 3306,
        'user'     => 'easytest',
        'password' => 'Rm.123456',
        'database' => 'db_easytest',
        'charset'  => 'utf8',
        'timeout'  => 2,
    ];

    $swoole_mysql->connect($server, function ($db, $r) use ($data, $fd)
    {
        $now = date("Y-m-d H:i:s");

        if ($r === false) {
            myLog($db->connect_errno." -- ".$db->connect_error);

            return;
        }

        $sql = "INSERT INTO `ts_data` (`raw`,`create_time`,`fd`) VALUES ('{$data}','{$now}','{$fd}');";
        $db->query($sql, function (swoole_mysql $db, $r)
        {
            if ($r === false) {
                myLog($db->error);
            } elseif ($r === true) {
            }

            $db->close();
        });

    });

    $swoole_mysql->on('close', function () use ($swoole_mysql)
    {
    });

    $serv->send($fd, "2");
});

//监听连接关闭事件
$serv->on('close', function ($serv, $fd)
{
    echo "Client: Close.\n";
});

//启动服务器
$serv->start();

function myLog($str) {
    $now = date("Y-m-d H:i:s");

    if (is_array($str)) {
        $str = var_export($str, true);
    }
    if (!is_string($str)) {
        return;
    }
    echo $now."\r\n".$str."\r\n\r\n";
}