<?php
session_start();
$code = isset($_POST['code'])?$_POST['code']:null;
if($code){
	if($code==$_SESSION['code']){
		echo '验证成功';
	}else{
		echo '验证失败';
	}
}
	

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>验证码demo</title>
</head>
<body>

	<img src="/test/codeimg.php" onclick='javascript:this.src="/test/codeimg.php?tm="+Math.random()'>
	<form action="" method='post'>
		<input type="text" name='code'>
	</form>
	
</body>
</html>
