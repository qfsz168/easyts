<?php

//创建Server对象，监听 127.0.0.1:9501端口
$serv = new swoole_server("0.0.0.0", 9501);

//监听连接进入事件
$serv->on('connect', function ($serv, $fd)
{
    //echo "Client: Connect.\n";
    $serv->send($fd, "1");
});

//监听数据发送事件
$serv->on('receive', function ($serv, $fd, $from_id, $data)
{
    global $a;
    if (!isset($a)) {
        $a = 1;
    }
    $a++;
    echo $a."\r\n";

    //    $serv->send($fd, "Server: ".$fd.'-----'.$data);
    $serv->send($fd, "2");
});

//监听连接关闭事件
$serv->on('close', function ($serv, $fd)
{
    echo "Client: Close.\n";
});


$db     = new swoole_mysql;
$server = [
    'host'     => 'localhost',
    'port'     => 3306,
    'user'     => 'root',
    'password' => 'Rm.123456',
    'database' => 'easyts',
    'charset'  => 'utf8',
    //指定字符集
    'timeout'  => 2,
    // 可选：连接超时时间（非查询超时时间），默认为SW_MYSQL_CONNECT_TIMEOUT（1.0）
];
$db->connect($server, function ($db, $r) use ($data)
{
    if ($r === false) {
        echo $db->connect_errno." -- ".$db->connect_error."\n";

        return;
    }

    $sql = "INSERT INTO `data` (`raw`) VALUES ({$data});";
    $db->query($sql, function (swoole_mysql $db, $r)
    {
        var_dump($r);
        if ($r === false) {
            var_dump($db->error, $db->errno);
        } elseif ($r === true) {
            var_dump($db->affected_rows, $db->insert_id);
        }

        $db->close();
    });
    $sql = "INSERT INTO `data` (`raw`) VALUES ({$data});";
    $db->query($sql, function (swoole_mysql $db, $r)
    {
        var_dump($r);
        if ($r === false) {
            var_dump($db->error, $db->errno);
        } elseif ($r === true) {
            var_dump($db->affected_rows, $db->insert_id);
        }

        $db->close();
    });
});

$db->on('close', function () use ($db)
{
    echo "--mysql is closed.\n";
});

//启动服务器
$serv->start();