<?php
// +----------------------------------------------------------------------
// | lidequan [ I CAN DO IT JUST WORK HARD ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.findme.wang All rights reserved.
// +----------------------------------------------------------------------
// | Author: lidequan <dequanli_edu@126.com> 
// +----------------------------------------------------------------------
namespace Common\Api;
class QQConnectApi extends OAuthApi{
    private $app_id = null;
    private $app_secret = null;
    private $callback = null;
    private $scope = null;
    private $token = null;
    private $uid = null;//又叫OpenID

    function __construct($app_id, $app_secret, $callback, $scope)
    {
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->callback = $callback;
        $this->scope = $scope;
    }  
     /**
     * 获取QQconnect Login 跳转到的地址值
     * @return array 返回包含code state 
    **/ 
     public function login()
     {
        $_SESSION['qq_state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
        $param = array(
                'response_type' => 'code',
                'client_id'     => $this->app_id,
                'redirect_uri'  => $this->callback,
                'state'         => $_SESSION['qq_state'],
                'scope'         => urlencode($this->scope)
            );
        $param = http_build_query($param); //会自动对url进行urlencode处理
        $url = 'https://graph.qq.com/oauth2.0/authorize?' . $param;        
        header('Location:' . $url);
     }
     /**
     * 请求URL地址，返回callback得到返回字符串
     * @param $url qq提供的api接口地址
     * */
     public function callback()
     {
        $code = $_GET['code'];
        $state = $_GET['state'];  //登录设置的sina_state
 
        if (empty($state) || $_SESSION['qq_state'] != $state) { //错误的回调
            return false;
        }
        //获取token
        $token = $this->getToken($code, $state);            
        if(empty($token)) {
            return false;
        } else {
            //获取openId
            return $this->getOpenId();
        }
     }
     /**
     * 获取access_token值
     * @param $code 
     * @param $state
     * @return Bool 
     * */
     public function getToken($code, $state)
     {
        $url = "https://graph.qq.com/oauth2.0/token";
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
        $rs = $Curl->sendGet();      
        parse_str($rs, $data);
        if(empty($data)) {
            return false;
        } else {         
            $this->token = $data["access_token"];
           return true;
        }
     }
     /**
     *获取openId
     */
     private function getOpenId()
     {
        $url = "https://graph.qq.com/oauth2.0/me";
        $param = array(
                "access_token"     =>    $this->token
            );
        $param = http_build_query($param);
        $url .= '?' . $param;
        $Curl = new CurlApi($url);
        $str = $Curl->sendGet();  
        if (strpos($str, "callback") !== false) {
            $lpos = strpos($str, "(");
            $rpos = strrpos($str, ")");
            $str  = substr($str, $lpos + 1, $rpos - $lpos -1);
        } 
        $data = json_decode($str, true);
        if(empty($data)) {
            return false;
        } else {         
            $this->uid = $data["openid"];
           return true;
        }
     }
     /**
    *获取用户信息
    *@param $val 
    */
    public function getUser($val = null){
        $url = 'https://graph.qq.com/user/get_user_info';
        $param = array(
            'access_token'       => $this->token,
            'oauth_consumer_key' => $this->app_id,
            'openid'             => $this->uid
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
            'uid'               => $this->uid,
            'type'              => 1,
            'id'                => $this->uid,
            'name'              => $data['nickname'],
            'gender'            => $data['gender'] == '女' ? '1' : '0',
            'profile_image_url' => $data['figureurl_2'],
            );
        return $packData;
    }
}