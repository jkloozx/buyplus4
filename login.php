<?php


$key = 'hellokang';
// 校验用户信息
// 也生成签名
$sign = md5('id=42' . $key);
if ($_GET['sign'] == $sign) {
    // 设置登录标志
    session_start();
    $_SESSION['user'] = ['user'=>'hellokang', 'id'=>42];
}