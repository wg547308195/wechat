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
        $url = 'http://'.$_SERVER['HTTP_HOST'];
        $target_url = substr($_SERVER['REQUEST_URI'], 1,strlen($_SERVER['REQUEST_URI']));

        $config = model('setting/SysSetting','service')->info();
        $options = [
            'app_id' => $config['wechat_app_id'],
            'secret' => $config['wechat_secret'],
            'token' => $config['wechat_token'],
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => $url.'/user/login/dologin?target='.$target_url,
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