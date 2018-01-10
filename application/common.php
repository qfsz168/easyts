<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


/**
 * 生成UUID
 * @author 王崇全
 * @date
 * @return string
 */
function uuid()
{
    if (function_exists('com_create_guid'))
    {
        return com_create_guid();
    }
    else
    {
        mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid   = substr($charid, 0, 8).$hyphen.substr($charid, 8, 4).$hyphen.substr($charid, 12, 4).$hyphen.substr($charid, 16, 4).$hyphen.substr($charid, 20, 12);

        return strtolower($uuid);
    }
}

//获取客户端的mac地址
function get_client_mac()
{
    @exec("arp -a", $array); // 执行arp -a命令，结果放到数组$array中
    foreach ($array as $value)
    {
        // 匹配结果放到数组$mac_array
        if (strpos($value, $_SERVER["REMOTE_ADDR"]) && preg_match("/(:?[0-9a-f]{2}[:-]){5}[0-9a-f]{2}/i", $value, $mac_array))
        {
            return $mac = $mac_array[0];
            break;
        }
    }

    return null;
}

// 不区分大小写的in_array实现
function in_array_case($value, $array)
{
    return in_array(strtolower($value), array_map('strtolower', $array));
}

/**
 * 遍历获取某路径下的文件,包括子文件夹
 * @author 王崇全
 * @param string $dir 目录名
 * @return array|null 包含完整文件路径级文件名的数组
 */
function get_files($dir)
{
    //如果本身就是个文件,直接返回
    if (is_file($dir))
    {
        return array($dir);
    }
    //创建数组,存储文件名
    $files = array();

    if (is_dir($dir) && ($dir_p = opendir($dir)))
    {//路径合法且能访问//创建目录句柄
        $ds = '/';  //目录分隔符
        while (($filename = readdir($dir_p)) !== false)
        {  //返回打开目录句柄中的一个条目
            if ($filename == '.' || $filename == '..')
            {
                continue;
            }  //排除干扰项
            $filetype = filetype($dir.$ds.$filename);  //获取本条目的类型(文件或文件夹)
            if ($filetype == 'dir')
            {  //如果收文件夹,
                $files = array_merge($files, get_files($dir.$ds.$filename));  //进行递归,并将结果合并到数组中
            }
            elseif ($filetype == 'file')
            {  //如果是文件,
                $files[] = mb_convert_encoding($dir.$ds.$filename, 'UTF-8', 'GBK');  //将文件名转成utf-8后存到数组
            }
        }
        closedir($dir_p);  //关闭目录句柄
    }
    else
    {//非法路径
        $files = null;
    }

    return $files;
}

/**
 * 获取毫秒精度的时间戳
 * @author 王崇全
 * @date
 * @return mixed
 */
function time_m()
{
    $mtime = explode(' ', microtime());

    return $mtime[1] + $mtime[0];
}

/**
 * 跨域资源共享 - 涉及安全性问题
 */
function crossdomain_cors()
{
    // 指定允许其他域名访问（所有）
    header('Access-Control-Allow-Origin:*');

    // 响应类型（所有：GET POST等）
    header('Access-Control-Allow-Methods:*');

    // 响应头设置（仅仅允许Content-Type）
    header('Access-Control-Allow-Headers:Content-Type');
    header('Access-Control-Allow-Credentials:true');
    header('Keep-Alive:timeout=5, max=100');
}

/**
 * 删除文件夹及其内部文件
 * @param $dir
 * @return bool
 */
function dir_del($dir)
{
    if (!is_dir($dir))
    {
        return false;
    }

    //先删除目录下的文件：
    $dh = opendir($dir);
    while ($file = readdir($dh))
    {
        if ($file != "." && $file != "..")
        {
            $fullpath = $dir."/".$file;
            if (!is_dir($fullpath))
            {
                @unlink($fullpath);
            }
            else
            {
                dir_del($fullpath);
            }
        }
    }
    closedir($dh);

    //删除当前文件夹：
    if (!@rmdir($dir))
    {
        return false;
    }

    return true;
}

/**
 * 递归地创建目录
 * @author 王崇全
 * @param string $pathname 路径
 * @param int    $mode     数字 1 表示使文件可执行，数字 2 表示使文件可写，数字 4 表示使文件可读。相加即$mode
 * @return bool
 */
function mk_dir($pathname, $mode = 0777)
{
    if (is_dir($pathname))
    {
        return true;
    }

    return mkdir($pathname, $mode, true);
}

/**
 * 将编号列表转为数组
 * @author 王崇全
 * @param string $ids 编号列表
 * @param string $sep 分隔符
 * @return array
 */
function ids2array(string $ids = null, string $sep = "|")
{
    if (!isset($ids) || $ids === "")
    {
        return null;
    }

    $arr = explode($sep, trim(trim($ids), $sep));
    if (empty($arr) || reset($arr) === "")
    {
        return [];
    }

    return $arr;
}

/**
 * 获取客户端的浏览器信息
 * @author 王崇全
 * @date
 * @param string $ua
 * @return array [名称,版本]
 */
function get_browse(string $ua)
{
    if (stripos($ua, "Firefox/") > 0)
    {
        preg_match("/Firefox\/([^;)]+)+/i", $ua, $b);
        $exp[0] = "Firefox";
        $exp[1] = $b[1]; //获取火狐浏览器的版本号
    }
    elseif (stripos($ua, "Maxthon") > 0)
    {
        preg_match("/Maxthon\/([\d\.]+)/", $ua, $aoyou);
        $exp[0] = "傲游";
        $exp[1] = $aoyou[1];
    }
    elseif (stripos($ua, "MSIE") > 0)
    {
        preg_match("/MSIE\s+([^;)]+)+/i", $ua, $ie);
        $exp[0] = "IE";
        $exp[1] = $ie[1]; //获取IE的版本号
    }
    elseif (stripos($ua, "OPR") > 0)
    {
        preg_match("/OPR\/([\d\.]+)/", $ua, $opera);
        $exp[0] = "Opera";
        $exp[1] = $opera[1];
    }
    elseif (stripos($ua, "Edge") > 0)
    {
        //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
        preg_match("/Edge\/([\d\.]+)/", $ua, $Edge);
        $exp[0] = "Edge";
        $exp[1] = $Edge[1];
    }
    elseif (stripos($ua, "Chrome") > 0)
    {
        preg_match("/Chrome\/([\d\.]+)/", $ua, $google);
        $exp[0] = "Chrome";
        $exp[1] = $google[1]; //获取google chrome的版本号
    }
    elseif (stripos($ua, 'rv:') > 0 && stripos($ua, 'Gecko') > 0)
    {
        preg_match("/rv:([\d\.]+)/", $ua, $IE);
        $exp[0] = "IE";
        $exp[1] = $IE[1];
    }
    else
    {
        $exp[0] = "";
        $exp[1] = "";
    }

    return [
        $exp[0],
        $exp[1],
    ];
}

/**
 * 获取操作系统类型
 * @author 王崇全
 * @date
 * @param string $agent
 * @return string
 */
function get_plat(string $agent)
{
    if (false !== stripos($agent, 'win') && stripos($agent, '95'))
    {
        $os = 'Windows 95';
    }
    else if (false !== stripos($agent, 'win 9x') && stripos($agent, '4.90'))
    {
        $os = 'Windows ME';
    }
    else if (false !== stripos($agent, 'win') && false !== stripos($agent, '98'))
    {
        $os = 'Windows 98';
    }
    else if (false !== stripos($agent, 'win') && false !== stripos($agent, 'nt 5.0'))
    {
        $os = 'Windows 2000';
    }
    else if (false !== stripos($agent, 'win') && false !== stripos($agent, 'nt 5.1'))
    {
        $os = 'Windows XP';
    }
    else if (false !== stripos($agent, 'win') && false !== stripos($agent, 'nt 5.2'))
    {
        $os = 'Windows XP';
    }
    else if (false !== stripos($agent, 'win') && false !== stripos($agent, 'nt 6.0'))
    {
        $os = 'Windows Vista';
    }
    else if (false !== stripos($agent, 'win') && false !== stripos($agent, 'nt 6.1'))
    {
        $os = 'Windows 7';
    }
    else if (false !== stripos($agent, 'win') && false !== stripos($agent, 'nt 6.2'))
    {
        $os = 'Windows 8';
    }
    else if (false !== stripos($agent, 'win') && false !== stripos($agent, 'nt 6.4'))
    {
        $os = 'Windows 10';
    }
    else if (false !== stripos($agent, 'win') && false !== stripos($agent, 'nt 10'))
    {
        $os = 'Windows 10';
    }
    else if (false !== stripos($agent, 'win') && false !== stripos($agent, 'nt'))
    {
        $os = 'Windows NT';
    }
    else if (false !== stripos($agent, 'win') && false !== stripos($agent, '32'))
    {
        $os = 'Windows 32';
    }
    else if (false !== stripos($agent, 'linux'))
    {
        $os = 'Linux';
    }
    else if (false !== stripos($agent, 'unix'))
    {
        $os = 'Unix';
    }
    else if (false !== stripos($agent, 'sun') && false !== stripos($agent, 'os'))
    {
        $os = 'SunOS';
    }
    else if (false !== stripos($agent, 'ibm') && false !== stripos($agent, 'os'))
    {
        $os = 'IBM OS/2';
    }
    else if (false !== stripos($agent, 'Mac') && false !== stripos($agent, 'PC'))
    {
        $os = 'Macintosh';
    }
    else if (false !== stripos($agent, 'PowerPC'))
    {
        $os = 'PowerPC';
    }
    else if (false !== stripos($agent, 'AIX'))
    {
        $os = 'AIX';
    }
    else if (false !== stripos($agent, 'HPUX'))
    {
        $os = 'HPUX';
    }
    else if (false !== stripos($agent, 'NetBSD'))
    {
        $os = 'NetBSD';
    }
    else if (false !== stripos($agent, 'BSD'))
    {
        $os = 'BSD';
    }
    else if (false !== stripos($agent, 'OSF1'))
    {
        $os = 'OSF1';
    }
    else if (false !== stripos($agent, 'IRIX'))
    {
        $os = 'IRIX';
    }
    else if (false !== stripos($agent, 'FreeBSD'))
    {
        $os = 'FreeBSD';
    }
    else if (false !== stripos($agent, 'teleport'))
    {
        $os = 'teleport';
    }
    else if (false !== stripos($agent, 'flashget'))
    {
        $os = 'flashget';
    }
    else if (false !== stripos($agent, 'webzip'))
    {
        $os = 'webzip';
    }
    else if (false !== stripos($agent, 'offline'))
    {
        $os = 'offline';
    }
    else
    {
        $os = '';
    }

    return $os;
}

/**
 * 构造 区间查询条件
 * @author 王崇全
 * @date
 * @param array       $map       查询条件数组
 * @param string      $filedName 字段名
 * @param string|int  $min       最小值
 * @param  string|int $max       最大值
 * @return void
 */
function sql_map_region(array &$map, string $filedName, $min, $max)
{
    if (isset($min) && !isset($max))
    {
        $map[$filedName] = [
            ">=",
            $min,
        ];
    }
    else if (!isset($min) && isset($max))
    {
        $map[$filedName] = [
            "<=",
            $max,
        ];
    }
    else if (isset($min) && isset($max))
    {
        $map[$filedName] = [
            "between",
            [
                $min,
                $max,
            ],
        ];
    }
}

/**
 * urlsafe_base64_encode
 *
 * @desc URL安全形式的base64编码
 *
 * @param string $str
 *
 * @return string
 */
function urlsafe_base64_encode($str)
{
    $find    = array(
        "+",
        "/",
        "=",
    );
    $replace = array(
        "-",
        "_",
        '',
    );

    return str_replace($find, $replace, base64_encode($str));
}

/**
 * 小文件下载
 * @author 王崇全
 * @date
 * @param string      $file
 * @param string|null $downloadFileName
 * @return void
 */
function download_file(string $file, string $downloadFileName = null)
{
    if (is_file($file))
    {
        if (!isset($downloadFileName))
        {
            $pathInfo         = pathinfo($file);
            $ext              = isset($pathInfo["extension"]) ? ".".$pathInfo["extension"] : "";
            $downloadFileName = $pathInfo["basename"].$ext;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: '.filesize($file));
        header("Accept-Length:".filesize($file));

        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua))
        {
            $encoded_filename = rawurlencode($downloadFileName);
            @header('Content-Disposition: attachment; filename="'.$encoded_filename.'"', false);
        }
        else if (preg_match("/Firefox/", $ua))
        {
            @header("Content-Disposition: attachment; filename*=\"utf8''".$downloadFileName.'"', false);
        }
        else
        {
            @header('Content-Disposition: attachment; filename="'.$downloadFileName.'"', false);
        }

        flush();
        readfile($file);
        exit;
    }
}

function rmBOM(string $string)
{
    if (substr($string, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf))
    {
        $string = substr($string, 3);
    }

    return $string;
}

function p($params)
{
    print_r("<pre>");
    print_r($params);
    print_r("</pre>");
}
