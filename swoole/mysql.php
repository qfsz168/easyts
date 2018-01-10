<?php
$swoole_mysql = new swoole_mysql;
$server       = [
    'host'     => 'localhost',
    'port'     => 3306,
    'user'     => 'root',
    'password' => 'Rm.123456',
    'database' => 'test',
    'charset'  => 'utf8',
    //指定字符集
    'timeout'  => 2,
    // 可选：连接超时时间（非查询超时时间），默认为SW_MYSQL_CONNECT_TIMEOUT（1.0）
];

$swoole_mysql->connect($server, "sql");

$swoole_mysql->on('close', function () use ($swoole_mysql)
{
    echo "--mysql is closed.\n";
});

/**
 * sql
 * @author 王崇全
 * @date
 * @param $db
 * @param $r
 * @return void
 */
function sql($db, $r) {
    if ($r === false) {
        die($db->connect_errno."--".$db->connect_error);
    }
    $sql = 'show tables';
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
}
