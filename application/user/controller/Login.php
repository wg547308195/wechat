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

    //经销商数据
    public function customs(){
        $list = model('custom/so_custom','service')->lists(['status'=>1],'create_time DESC',false,false,true);
        if ($list->isEmpty()){
            return $this->result('',0,'暂无数据');
        }
        $return = [];
        foreach ($list as $key => $value) {
            $return[$key]['value'] = $value['id'];
            $return[$key]['text'] = $value['nickname'];
        }
        return $this->result($return,200);
    }

    //注册
    public function register(Request $request){
        $id = $request->param('id');
        $user = $this->service->detail(['id'=>$id]);
        if ($request->isAjax()){
            //验证
            $validate = $this->validate($request->post(), 'app\user\validate\SoUser.register');
            if (true !== $validate) {
                return $this->result('',0,$validate);
            }
            //验证验证码
            $valid_code = model('message/Sms','service')->check_verification_code('SMS_155190221',$request->post('code'),$request->post('mobile'));
            if (!$valid_code){
                return $this->result('',0,model('message/Sms','service')->getError());
            }
            $data = $request->post();
            $data['openid'] = $user['openid'];

            $result = $this->service->register($data);
            if ($result === false){
                return $this->result('',0,$this->service->getError());
            }
            return $this->result($result,200,'注册成功');
        }
        return $this->fetch('register');
    }

    /**
     * 用户同意授权登录，获取code并跳转
     */
    public function login() {
        
        \Cache::set('wechat_code','');
        $config = model('setting/SysSetting','service')->info();
        $options = [
            'app_id' => $config['wechat_app_id'],
            'secret' => $config['wechat_secret'],
            'token' => $config['wechat_token'],
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => url('/user/login/dologin','','',true),
            ]
        ];
        $app = Factory::officialAccount($options);
        $oauth = $app->oauth;
        \Log::write("[oauth]".print_r($oauth, true), 'debug');

        return $oauth->redirect();
    }

    /**
     * 微信授权回调
     */
    public function dologin() {
        $result = $this->service->login();
        \Log::write("[666]".print_r($result, true), 'debug');
        if ($result === false){
            return $this->result('',0,$this->service->getError());
        }
        \Log::write("[收到授权回调]".print_r($result, true), 'debug');
        $redirect_url = cookie('redirect_url') ? cookie('redirect_url') : url('user/my/index','','',true);

        $this->redirect($redirect_url);
    }

    //获取验证码
    public function get_vcode(Request $request){
        if ($request->isAjax()){
            $info = model('user/so_user')->where('id','neq',$request->post('id'))->where('mobile','=',$request->post('mobile'))->find();
            if (!empty($info->id)){
                return $this->result('',0,'手机号已被使用');
            }
            $result = model('message/Sms','service')->send_one($request->post('mobile'), 'SMS_155190221', [], 1);
            if ($result === false){
                return $this->result('',0,'发送失败，请稍后再试');
            }
            return $this->result($result,200,'验证码发送成功');
        }
    }

}