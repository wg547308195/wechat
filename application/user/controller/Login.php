<?php
namespace app\user\controller;

use think\Controller;
use think\Request;
use EasyWeChat\Factory;

class Login extends Controller
{

    protected function initialize()
    {
        $this->service = model('user/so_user','service');
    }

    //用户同意授权登录，获取code
    public function login() {
        $options = [
            'app_id' => config('wechat.app_id'),
            'secret' => config('wechat.secret'),
            'token' => config('wechat.token'),
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => '/user/login/dologin',
            ]
        ];
        $app = Factory::officialAccount($options);
        $oauth = $app->oauth;

        return $oauth->redirect();
    }

    /**
     * 用户注册&登录
     */
    public function dologin() {
        $result = $this->service->login();
        if ($result === false){
            return $this->result('',0,$this->service->getError());
        }
        $redirect_url = cookie('redirect_url') ? cookie('redirect_url') : url('user/my/index','','',true);
        $this->redirect($redirect_url);
    }

}