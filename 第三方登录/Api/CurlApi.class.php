<?php
// +----------------------------------------------------------------------
// | lidequan [ I CAN DO IT JUST WORK HARD ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.findme.wang All rights reserved.
// +----------------------------------------------------------------------
// | Author: lidequan <dequanli_edu@126.com> 
// +----------------------------------------------------------------------
namespace Common\Api;

class CurlApi{
	private $url;
	private $handle;
	private $outopt;

	function __construct($url=null){
		$this->url=$url;
		$this->handle=curl_init();
		curl_setopt($this->handle,CURLOPT_RETURNTRANSFER,1);
	}

	//发送post请求
	public function sendPost($data){
		curl_setopt($this->handle,CURLOPT_POST,1);//模拟POST
		curl_setopt($this->handle,CURLOPT_POSTFIELDS,$data);//POST内容
		$this->query();
		return $this->outopt;
	}
	//发送get请求
	public function sendGet(){
		$this->query();
		return $this->outopt;			
	}

	public function query(){
		curl_setopt($this->handle,CURLOPT_URL,$this->url);
		$this->outopt = curl_exec($this->handle);
		curl_close($this->handle);
	}
	//设置相应时间
	public function setTimeOut()
	{

	}

	public function setUrl($url){
		$this->url=$url;
	}

	public function getUrl($url)
	{

	}



}

