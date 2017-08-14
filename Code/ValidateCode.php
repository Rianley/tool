<?php
//验证码类
class ValidateCode{
	//随机因子
	private $charset = 'abcdefghijkmnprstuvwxyABCDEFGHJKLMNPQRSTUVWXY345678';
	private $code='';   //验证码
	private $codelen;  	//验证码长度
	private $width;	//画布宽
	private $height;	//画布高
	private $img;			//图形资源句柄
	private $font;			//字体
	private $fontSize;		//字体大小
	private $fontColor;		//字体颜色

	//构造方法初始化
	public function __construct($codelen=4,$width=130,$height=50,$fontSize=20){
		$this->font = dirname(__FILE__).'/font/ebrima.ttf';
		$this->codelen = $codelen;
		$this->width = $width;
		$this->height = $height;
		$this->fontSize = $fontSize;
		$this->createCode();
	}
	//生成随机码
	private function createCode(){
		$_len = strlen($this->charset)-1;
		for($i=0;$i<$this->codelen;$i++){
			$this->code .=$this->charset[mt_rand(0,$_len)];
		}
		$_SESSION['code'] = strtolower($this->code);  //将生成的code码保存在session中
	}

	//创建背景
	private function createBg(){
		$this->img = imagecreatetruecolor($this->width, $this->height);
		$color = imagecolorallocate($this->img, mt_rand(157,255), mt_rand(157,255), mt_rand(157,255));
		imagefilledrectangle($this->img, 0, $this->height, $this->width, 0, $color);
	}

	//生成文字
	private function createFont(){
		$_x = $this->width/$this->codelen;
		for($i=0;$i<$this->codelen;$i++){
			$this->fontColor = imagecolorallocate($this->img, mt_rand(0,156), mt_rand(0,156), mt_rand(0,156));
			imagettftext($this->img, $this->fontSize, mt_rand(-30,30), $_x*$i+mt_rand(3,5), $this->height/1.4, $this->fontColor, $this->font, $this->code[$i]);
		}
	}

	//生成线条，雪花
	private function createLine(){
		for($i=0;$i<6;$i++){
			$color = imagecolorallocate($this->img, mt_rand(0,156), mt_rand(0,156), mt_rand(0,156));
			imageline($this->img, mt_rand(0,$this->width), mt_rand(0,$this->height), mt_rand(0,$this->width), mt_rand(0,$this->height), $color);

		}
		for ($i=0; $i < 100; $i++) { 
			$color = imagecolorallocate($this->img, mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));
			imagestring($this->img, mt_rand(1,5), mt_rand(0,$this->width), mt_rand(0,$this->height), '*', $color);
		}
	}

	//输出
	private function outPut(){
		header('Content-type:image/png');
		imagepng($this->img);
		imagedestroy($this->img);
	}

	//对外生成
	public function doimg(){
		$this->createBg();
		$this->createLine();
		$this->createFont();
		$this->outPut();

	}
}	


?>