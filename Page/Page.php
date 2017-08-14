<?php
//分页类
class Page{
	private $total;  //总记录
	private $pagesize; //每页显示条数
	private $limit;  //limit
	private $page;   //当前页码
	private $pagenum;//获取总页码数
	private $url;    //地址
	private $bothnum;//两边保持数量
	public function __construct($total,$pagesize){
		$this->total = $total?$total:1;
		$this->pagesize = $pagesize;
		$this->pagenum = ceil($this->total/$this->pagesize);
		$this->page = $this->setPage();
		$this->limit = "LIMIT ".($this->page-1)*$this->pagesize.",".$this->pagesize;
		$this->url = $this->setUrl();
		$this->bothnum = defined('BOTHNUM')?BOTHNUM:5;
	}


	//拦截器
	public function __get($key){
		return $this->$key;
	}


	//分页信息
	public function showpage(){
		$page='';
		$page .= $this->first();
		$page .= $this->pageList();
		$page .= $this->last();
		$page .= $this->prev();
		$page .= $this->next();
		
		return $page;
	}


	//获取当前页面
	private function setPage(){
		$num='';
		if(!empty($_GET['page'])){
			if($_GET['page']>0){
				if($_GET['page']>$this->pagenum){
					$num=$this->pagenum;
				}else{
					$num=$_GET['page'];
				}
			}else{
				$num=1;
			}
		}else{
			$num=1;
		}
		return $num;
	}

	//首页
	private function first(){
		if($this->page>$this->bothnum+1){
			return "<a href='".$this->url."'>1</a>...";
		}
		
	}

	//上一页
	private function prev(){
		if($this->page==1){
			return "<span class='disabled'>上一页</span>";
		}
		return "<a href='".$this->url."&page=".($this->page-1)."'>上一页</a>";
	}

	//下一页
	private function next(){
		if($this->page==$this->pagenum){
			return "<span class='disabled'>下一页</span>";
		}
		return "<a href='".$this->url."&page=".($this->page+1)."'>下一页</a>";
	}

	//尾页
	private function last(){
		if($this->pagenum-$this->page>$this->bothnum){
			return "...<a href='".$this->url."&page=".$this->pagenum."'>{$this->pagenum}</a>";
		}
		
	}

	//获取地址
	private function setUrl(){
		$url = $_SERVER['REQUEST_URI'];
		$par = parse_url($url);
		if(isset($par['query'])){
			parse_str($par['query'],$_query);
			unset($_query['page']);
			$url = $par['path'].'?'.http_build_query($_query);
		}else{
			$url = $url.'?';
		}
		return $url;
	}

	//数字目录
	private function pageList(){
		$_pagelist='';
		//当前页前面内容
		for ($i =$this->bothnum;$i>=1; $i--) {
			$_page = $this->page-$i;
			if($_page<1)continue;
			$_pagelist .= "<a href='".$this->url."&page=".$_page."'>".$_page."</a>";
		} 
		//当前页
		$_pagelist .= '<span class="me">'.$this->page.'</span>';
		//当前页后面内容
		for ($i=1; $i <=$this->bothnum ; $i++) { 
			$_page = $this->page+$i;
			if($_page>$this->pagenum)break;
			$_pagelist .= "<a href='".$this->url."&page=".$_page."'>".$_page."</a>";
		}
		return $_pagelist;
	}
}
?>