<?php
include 'ValidateCode.php';//引入验证码类
session_start();   //实例化验证码类前要先开启session
$code = new ValidateCode();    //实例化验证码类
$code->doimg();			//将生成的验证码图片输出
?>