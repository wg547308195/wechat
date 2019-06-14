<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;

class Login extends Controller
{
    /**
     * 登录页
     */
    public function index()
    {
        return $this->fetch('index');
    }

    /**
     * 登录处理
     */
    public function do_login(Request $request)
    {
        $validate = $this->validate($request->post(), 'app\admin\validate\SoAdmin.login');
        if (true !== $validate) {
            $this->error($validate);
        }
        $data = [];
        $data['username'] = $request->post('account');
        $data['password'] = $request->post('password');
        $result = model('admin/so_admin','service')->login($data);
        if ($result === false){
            $this->error(model('admin/so_admin','service')->getError());
        }
        $this->success('登陆成功','index/index');
    }

    /**
     * 退出登陆
     */
    public function logout(Request $request){
        cookie('account_token',null);
        $this->success('退出登陆成功','login/index','',1);
    }
}