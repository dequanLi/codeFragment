<?php
// +----------------------------------------------------------------------
// | lidequan [ I CAN DO IT JUST WORK HARD ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.findme.wang All rights reserved.
// +----------------------------------------------------------------------
// | Author: lidequan <dequanli_edu@126.com> 
// +----------------------------------------------------------------------

namespace Common\Api;
abstract class OAuthApi{
	//登录
	abstract protected function login();
	abstract protected function callback();
	abstract protected function packData();
}