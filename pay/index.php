<?php 

header('HTTP/1.1 301 Moved Permanently');
if(strstr($_SERVER['HTTP_USER_AGENT'], 'QQ/')){
header('Location: https://www.bunian.cn/shoukuan/erweima.php?app=qq');
}ELSE IF(strstr($_SERVER['HTTP_USER_AGENT'], 'Alipay')){
	header('Location: HTTPS://QR.ALIPAY.COM/FKX05527ZIDMAKPD4OOZDD');
	
}ELSE IF(strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger/')){

header('Location: https://www.bunian.cn/shoukuan/erweima.php?app=weixin');
}





?>  
