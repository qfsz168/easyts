<?php

// [ 应用入口文件 ]

//**开启严格模式
declare(strict_types = 1);

// [ 应用入口文件 ]
namespace think;

// ***float和double型数据序列化存储时的精度(有效位数，-1表示使用实际值)
ini_set('serialize_precision', '-1');

// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';


// 支持事先使用静态方法设置Request对象和Config对象

// 执行应用并响应
Container::get('app')->bind('workman/socket')->run()->send();