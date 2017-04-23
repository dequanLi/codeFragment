<?php
// +----------------------------------------------------------------------
// | lidequan [ I CAN DO IT JUST WORK HARD ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.findme.wang All rights reserved.
// +----------------------------------------------------------------------
// | Author: lidequan <dequanli_edu@126.com> 
// +----------------------------------------------------------------------
namespace Common\Api;

class WeiboConnectApi extends OAuthApi{
    private $app_id = null;
    private $app_secret = null;
    private $callback = null;
    private $scope = null;
    private $token = null;
    private $uid = null;

    function __construct($app_id, $app_secret, $callback, $scope)
    {
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->callback = $callback;
        $this->scope = $scope;
    }   
     /**
     * 获取weiboconnect Login 跳转到的地址值
     * 参考：http://open.weibo.com/wiki/Oauth2/authorize
    **/ 
    public function login()
    {
        $_SESSION('sina_state', md5(uniqid(rand(), TRUE)));//CSRF protection   
        $login_url = 'https://api.weibo.com/oauth2/authorize?response_type=code&client_id=' 
            .$this->app_id. '&redirect_uri=' . urlencode($this->callback)
            . '&state=' . $_SESSION('sina_state')
            .'&scope='.$this->scope;
        //显示出登录地址
         header('Location:'.$login_url);
    }

    /**
     * 用户授权后的回调函数，根据参数
     * @param  
     * */
    
    public function callback() {
        $code = $_GET['code'];
        $state = $_GET['state'];  //登录设置的sina_state
        if (empty($state) || $_SESSION('sina_state') != $state) { //错误的回调
            return false;
        }
        //获取token
        $token = $this->getToken($code, $state);       
        if(empty($token)) {
            return false;
        } else {
            return true;
        }
    }
    /**
    *获取token
    *设置token和opendId
    * @param $code 
    * @param $state
    */
    public function getToken($code, $state)
    {
         $url = "https://api.weibo.com/oauth2/access_token";
         $param = array(
                "client_id"     =>    $this->app_id,
                "client_secret" =>    $this->app_secret,
                "grant_type"    =>    "authorization_code",
                "code"          =>    $code,
                "state"         =>    $state,
                "redirect_uri"  =>    $this->callback
            );
        $param = http_build_query($param);
        $url .= '?' . $param;
        $Curl = new CurlApi($url);
        $data = $Curl->sendPost($param);     
        $data = json_decode($data, true);
        if(empty($data)) {
            return false;
        } else {         
            $this->token = $data["access_token"];
            $this->uid = $data["uid"];
           return true;
        }
    }
    /**
    *获取用户信息
    *@param $val 
    */
    public function getUser($val = null){
        $url = 'https://api.weibo.com/2/users/show.json';
        $param = array(
            'access_token' => $this->token,
            'uid' => $this->uid
        );
        $param = http_build_query($param);
        $url .= '?' . $param;
        $Curl = new CurlApi($url);
        $data = $Curl->sendGet();
        
        $data = json_decode($data, true);
        if (empty($val) || ! isset($data[$val])) {
            return $data;
        } else {
            return $data[$val];
        }
    }
    /**
    * 对数据进行包装处理，便于统一数据处理
    */
    public function packData()
    {
        $data = $this->getUser();
        $packData = array(
            'uid'               => $data['id'],
            'type'              => 2,
            'id'                => $data['id'],
            'name'              => $data['name'],
            'gender'            => $data['gender'] == 'f' ? 1 : 0,
            'profile_image_url' => $data['profile_image_url'],
            );
        return $packData;
    }
}