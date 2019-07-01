<?php
namespace app\user\controller;

use think\Request;

class My extends Init
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function index(){
        $goods = model('goods/so_goods','service')->lists([],'update_time DESC',false,false);
        $this->assign('goods',$goods);
        return $this->fetch('index');
    }

    public function register(Request $request){
        if ($this->user['custom_id'] > 0){
            return $this->redirect(url('user/my/index'));
        }
        if ($request->isAjax()){
            $validate = $this->validate($request->post(), 'app\user\validate\SoUser.register');
            if (true !== $validate) {
                return $this->result('',0,$validate);
            }
            $data = $request->post();
            $data['openid'] = $this->user['openid'];

            $result = model('user/so_user','service')->register($data);
            if ($result === false){
                return $this->result('',0,model('user/so_user','service')->getError());
            }
            return $this->result($result,200,'注册成功');
        }
        return $this->fetch('register');
    }

    //经销商数据
    public function customs(){
        $list = model('custom/so_custom','service')->lists([],'create_time DESC',false,false,true);
        if ($list->isEmpty()){
            return $this->result('',0,'暂无数据');
        }
        return $this->result($list,200);
    }
}