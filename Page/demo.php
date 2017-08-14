<?php
include 'Page.php';//引入分页类
define('BOTHNUM', 6);//当前页连边显示数量，不设置则为默认值
$page = new Page(123,10);//实例化分页类，第一个参数为总记录条数，第二个参数为每页显示条数
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>分页demo</title>
	<style>
		a,span{
			color: #999;
			text-decoration: none;
			display: inline-block;
			padding: 0 10px;
			margin: 0 6px;
			border-radius: 3px;
			height: 26px;
			line-height: 26px;
			border: 1px solid #666;
		}
		span{
			border: none;
		}
	</style>
</head>
<body>
	<?= $page->showpage(); //调用?>
</body>
</html>
