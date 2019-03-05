<?php

defined('BASEPATH') OR exit('No direct script access allowed');

//开启调试
$CI = &get_instance();
$CI->output->enable_profiler(false);

if (!function_exists('deCodeBitMap')) {
    /**
     * api请求有效性验证
     * @param $username
     * @param $token
     * @param $timestamp
     * @return bool
     */
    function deCodeBitMap($imagepath,$host,$port){
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die($imagepath." Could not connet server create\n"); // 创建一个Socket
        if(!$socket){
            return "";
        }
        $connection = socket_connect($socket, $host, $port) or die($imagepath." Could not connet server connection\n");    //  连接
        if(!$connection){
            return "";
        }
        socket_write($socket, $imagepath) or die("Write failed\n"); // 数据传送 向服务器发送消息

        $buff = socket_read($socket, 1024, PHP_NORMAL_READ);
        return $buff;
    }
}