<!doctype html>
<html>
<head>
	<meta charset="UTF-8" />
	<title>支付</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
    <meta content="email=no" name="format-detection">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <style type="text/css">
      *{
        margin: 0;
        padding: 0;
      }
      body{
        background: #f2f2f2;
      }
      .pay_head .banner{
        width: 100%;
        display: block;
      }
      .pay-content{
        padding: 25% 7.8125%;
        background: #54bc6e;
      }
      .pay-codeimg{
        width: 100%;
      }
      .pay-tips{
        padding-top: 9.375%;
        text-align: center;
        color: #fff;
      }
    </style>
</head>
<body>
  <div class="pay_head">
    <img class="banner" src="<?
 
		if($_GET['app']=="qq"){
		ECHO 'qqpay_head.jpg';
		}else{
		ECHO 'weixinpay_head.jpg';
		}
?>"/>
  </div>
  <div class="pay-content">
      <div class="">
        <img class="pay-codeimg" src="logo.png"/>
        <div class="pay-tips">长按二维码识别,向商家付款</div>
      </div>
  </div>

</body>
</html>
