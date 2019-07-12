<?php
namespace app\custom\controller;

use think\Controller;
use think\Request;

class Login extends Controller
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function index(){
        $focus = model('setting/sys_focus','service')->lists(['status'=>1],'sort ASC',false,false);
        $this->assign('focus',$focus);
        return $this->fetch('index');
    }

    /**
     * 登录处理
     */
    public function do_login(Request $request)
    {
        $validate = $this->validate($request->post(), 'app\custom\validate\SoCustom.login');
        if (true !== $validate) {
            return $this->result('',0,$validate);
        }
        $data = [];
        $data['username'] = $request->post('account');
        $data['password'] = $request->post('password');
        $result = model('custom/so_custom','service')->login($data);
        if ($result === false){
            return $this->result('',0,model('custom/so_custom','service')->getError());
        }
        return $this->result($result,200,'登陆成功');
    }

    /**
     * 退出登陆
     */
    public function logout(Request $request){
        cookie('custom_token',null);
        return $this->result($result,200,'退出登陆成功');
    }

    
}