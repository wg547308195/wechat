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

    public function bind(Request $request){
        if ($this->user['custom_id'] > 0){
            return $this->redirect(url('user/my/index'));
        }
        if ($request->isAjax()){
            //验证
            $validate = $this->validate($request->post(), 'app\user\validate\SoUser.bind');
            if (true !== $validate) {
                return $this->result('',0,$validate);
            }
            $openid = $this->user['openid'];
            $custom_id = $request->post('custom_id',0);
            $mobile = $request->post('mobile','');

            $result = model('user/so_user','service')->bind($openid, $custom_id, $mobile);
            if ($result === false){
                return $this->result('',0,model('user/so_user','service')->getError());
            }
            //注意：此处状态码一定和前端配置的状态码相同，否则数据会出问题(正常返回200，错误返回0)
            return $this->result($result,200,'绑定成功');
        }
        return $this->fetch('bind');
    }

    //经销商数据
    public function customs(){
        $list = model('custom/so_custom','service')->lists([],'create_time DESC',false,false,true);
        if ($list->isEmpty()){
            return $this->result('',0,'暂无数据');
        }
        //注意：此处状态码一定和前端配置的状态码相同，否则数据会出问题(正常返回200，错误返回0)
        return $this->result($list,200);
    }
}