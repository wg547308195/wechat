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

        return $this->fetch('index');
    }

    //个人资料修改页
    public function profile(Request $request){
        if ($request->isAjax()){
            //验证
            $validate = $this->validate($request->post(), 'app\user\validate\SoUser.edit');
            if (true !== $validate) {
                return $this->result('',0,$validate);
            }
            $result = model('user/so_user','service')->save($request->post(),$this->user['id']);
            if ($result === false){
                return $this->result('',0,model('user/so_user','service')->getError());
            }
            return $this->result($result,200,'修改成功');
        }
        return $this->fetch('profile');
    }

    //产品列表
    public function goods(Request $request){
        $focus = model('setting/sys_focus','service')->lists(['status'=>1],'sort ASC',false,false);
        $user_goods = model('user/so_user_goods','service')->lists(['uid'=>$this->user['id']],'create_time DESC',false,false,true,['user','goods'],['surplus_time']);
        if (!empty($user_goods)){
            foreach ($user_goods as $key => $value) {
                $user_goods[$key]['_red_circle'] = 0;
                $goods_orders = model('user/so_user_order')->where('user_goods_id','=',$value['id'])->where('uid','=',$value['uid'])->where('status','=',0)->select();
                if (count($goods_orders) > 0){
                    $user_goods[$key]['_red_circle'] = 1;
                }
            }
        }
        $this->assign('user_goods',$user_goods);
        $this->assign('focus',$focus);
        return $this->fetch('goods');
    }

    //产品详情
    public function goods_detail(Request $request){
        $user_goods = model('user/so_user_goods','service')->detail(['id'=>$request->param('id')],true,[],['surplus_time']);
        $user_orders = model('user/so_user_order','service')->lists(['user_goods_id'=>$user_goods['id'],'uid'=>$user_goods['uid']],'create_time DESC',false,false,true,['user','user_goods']);
        $this->assign('user_goods',$user_goods);
        $this->assign('user_orders',$user_orders);
        return $this->fetch('goods_detail');
    }

    //添加商品
    public function add_goods(Request $request){
        if ($request->isAjax()){
            $data = [];
            $data['goods_id'] = $request->post('goods_id');
            $data['uid'] = $this->user['id'];
             //验证
            $validate = $this->validate($data, 'app\user\validate\SoUserGoods.add');
            if (true !== $validate) {
                return $this->result('',0,$validate);
            }
            $result = model('user/so_user_goods','service')->create($data);
            if ($result === false){
                return $this->result('',0,model('user/so_user_goods','service')->getError());
            }
            return $this->result($result,200,'添加成功');
        }
        return $this->fetch('profile');
    }

    //获取平台所有上架产品
    public function get_goods(Request $request){
        $goods = model('goods/so_goods','service')->lists(['status'=>1],'update_time DESC',false,false);
        if ($goods->isEmpty()){
            return $this->result('',0,'暂无数据');
        }
        $return = [];
        foreach ($goods as $key => $value) {
            $return[$key]['value'] = $value['id'];
            $return[$key]['text'] = $value['name'];
        }
        return $this->result($return,200);
    }

    //添加维保
    public function add_order(Request $request){
        $data = [];
        $data['user_goods_id'] = $request->post('goods_id');
        $data['desc'] = $request->post('desc');
        $data['uid'] = $this->user['id'];
         //验证
        $validate = $this->validate($data, 'app\user\validate\SoUserOrder.add');
        if (true !== $validate) {
            return $this->result('',0,$validate);
        }
        $result = model('user/so_user_order','service')->create($data);
        if ($result === false){
            return $this->result('',0,model('user/so_user_order','service')->getError());
        }
        return $this->result($result,200,'申请成功');
    }
}