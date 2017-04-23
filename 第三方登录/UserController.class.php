<?php
// +----------------------------------------------------------------------
// | lidequan [ I CAN DO IT JUST WORK HARD ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.findme.wang All rights reserved.
// +----------------------------------------------------------------------
// | Author: lidequan <dequanli_edu@126.com> 
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Api\UserApi;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserController extends HomeController {

	/* 退出登录 */
	public function logout(){
		if(is_login()){
			D('Member')->logout();
			$forward = getLastIp();
			$this->redirect($forward);
		} else {
			$this->redirect('User/login');
		}
	}
    /**
    *第三方登录回调地址
    */
    public function oAuth()
    {
    	$type = I('type');
    	switch ($type) {
    		case 'sina':
    			$app_id = C('SINA_AUTH.APP_ID');
			    $scope = C('SINA_AUTH.SCOPE');
			    $callback = C('SINA_AUTH.CALLBACK');
			    $app_secret = C('SINA_AUTH.APP_KEY');
			    $sns = new \Common\Api\WeiboConnectApi($app_id,$app_secret, $callback, $scope);
    			break;
    		case 'qq':
    			$app_id = C('QQ_AUTH.APP_ID');
			    $scope = C('QQ_AUTH.SCOPE');
			    $callback = C('QQ_AUTH.CALLBACK');
			    $app_secret = C('QQ_AUTH.APP_KEY');
			    $sns = new \Common\Api\QQConnectApi($app_id,$app_secret, $callback, $scope);
    			break;
    		default:
    			
    			break;
    	}
    	$status = $sns->callback();
    	if(empty($status)) {
    		//token获取失败，重新授权
    		$sns->login();
    	} else {
    		$userInfo = $sns->packData(); //获取第三方数据
    		//注册登录    		
    		$Member = D('Member');
    		$Member->oAuthLogin($userInfo); //将第三方数据保存到数据库
    		$forward = session('login_forward');//获取需要跳转的网址
    		$this->redirect($forward);
    	}
    }
    /**
    *第三方登录
    */
    public function oAuthLogin(){
    	$type = I('type', null);
    	$sns = null;
    	switch ($type) {
    		case 'sina':
    			$app_id = C('SINA_AUTH.APP_ID');
			    $scope = C('SINA_AUTH.SCOPE');
			    $callback = C('SINA_AUTH.CALLBACK');
			    $app_secret = C('SINA_AUTH.APP_KEY');
			    $sns = new \Common\Api\WeiboConnectApi($app_id, $app_secret, $callback, $scope);
    			break;
    		case 'qq':
    			$app_id = C('QQ_AUTH.APP_ID');
			    $scope = C('QQ_AUTH.SCOPE');
			    $callback = C('QQ_AUTH.CALLBACK');
			    $app_secret = C('QQ_AUTH.APP_KEY');
			    $sns = new \Common\Api\QQConnectApi($app_id, $app_secret, $callback, $scope);
    			break;
    		default:
    			break;
    	}
    	session('login_forward', getLastIp()); //缓存上一页的网址
		$sns->login();
    }
}
