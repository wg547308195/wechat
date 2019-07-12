<?php
namespace app\custom\controller;

use think\Request;

class Index extends Init
{
    protected function initialize()
    {
        parent::initialize();
        $this->service = model('custom/so_custom','service');
    }

    public function index(){
        
        return $this->fetch('index');
    }

    //我的客户
    public function user(Request $request){
        $user_list = model('user/so_user','service')->lists(['custom_id'=>$this->custom['id'],'status'=>1],'create_time DESC',false,false);
        $total_user_order = 0;
        if (!empty($user_list)){
            foreach ($user_list as $key => $value) {
                $user_list[$key]['_red_circle'] = 0;
                $user_orders = model('user/so_user_order')->where('uid','=',$value['id'])->where('status','=',0)->select();
                if (count($user_orders) > 0){
                    $total_user_order = $total_user_order + count($user_orders);
                    $user_list[$key]['_red_circle'] = 1;
                }
            }
        }
        $this->assign('total_user_order',$total_user_order);
        $this->assign('user_list',$user_list);
        return $this->fetch('user');
    }

    //客户详情
    public function user_detail(Request $request){
        $user = model('user/so_user','service')->detail(['id'=>$request->param('id')]);
        $user_goods = model('user/so_user_goods','service')->lists(['uid'=>$user['id']],'create_time DESC',false,false,true,['user','goods'],['surplus_time']);
        if (!empty($user_goods)){
            foreach ($user_goods as $key => $value) {
                $user_goods[$key]['_red_circle'] = 0;
                $goods_orders = model('user/so_user_order')->where('user_goods_id','=',$value['id'])->where('uid','=',$value['uid'])->where('status','=',0)->select();
                if (count($goods_orders) > 0){
                    $user_goods[$key]['_red_circle'] = 1;
                }
            }
        }
        $this->assign('user',$user);
        $this->assign('user_goods',$user_goods);
        return $this->fetch('user_detail');
    }

    //客户产品详情
    public function goods_detail(Request $request){
        $user_goods = model('user/so_user_goods','service')->detail(['id'=>$request->param('id')],true,[],['surplus_time']);
        $user_orders = model('user/so_user_order','service')->lists(['user_goods_id'=>$user_goods['id'],'uid'=>$user_goods['uid']],'create_time DESC',false,false,true,['user','user_goods']);
        $this->assign('user_goods',$user_goods);
        $this->assign('user_orders',$user_orders);
        return $this->fetch('goods_detail');
    }

    //个人资料修改页
    public function profile(Request $request){
        if ($request->isAjax()){
        	//验证
	        $validate = $this->validate($request->post(), 'app\custom\validate\SoCustom.edit');
	        if (true !== $validate) {
	            return $this->result('',0,$validate);
	        }
	    	$result = $this->service->save($request->post(),$this->custom['id']);
	    	if ($result === false){
	    		return $this->result('',0,$this->service->getError());
	    	}
	    	return $this->result($result,200,'修改成功');
        }
        return $this->fetch('profile');
    }

    //修改手机
    public function change_mobile(Request $request){
        if ($request->isAjax()){
            //验证
            $validate = $this->validate($request->post(), 'app\custom\validate\SoCustom.change_mobile');
            if (true !== $validate) {
                return $this->result('',0,$validate);
            }
            $valid_code = model('message/Sms','service')->check_verification_code('SMS_155190221',$request->post('code'),$request->post('mobile'));
            if (!$valid_code){
                return $this->result('',0,model('message/Sms','service')->getError());
            }
            $data = [];
            $data['mobile'] = $request->post('mobile');
            $result = $this->service->save($data,$this->custom['id']);
            if ($result === false){
                return $this->result('',0,$this->service->getError());
            }
            return $this->result($result,200,'修改成功');
        }
        return $this->fetch('change_mobile');
    }

    //修改密码
    public function change_password(Request $request){
        if ($request->isAjax()){
            //验证
            $validate = $this->validate($request->post(), 'app\custom\validate\SoCustom.change_password');
            if (true !== $validate) {
                return $this->result('',0,$validate);
            }
            if($this->custom['password'] !== md5(md5($request->post('old_password')) . $this->custom['salt'])){
                return $this->result('',0,'旧密码错误');
            }
            if ($request->post('new_password') != $request->post('re_password')){
                return $this->result('',0,'两次输入的密码不一致');
            }
            $data = [];
            $data['password'] = $request->post('new_password');
            $result = $this->service->save($data,$this->custom['id']);
            if ($result === false){
                return $this->result('',0,$this->service->getError());
            }
            return $this->result($result,200,'修改成功');
        }
        return $this->fetch('change_password');
    }

    //处理维保单
    public function check_order(Request $request){
        if ($request->isAjax()){
            //验证
            $validate = $this->validate($request->post(), 'app\user\validate\SoUserOrder.handle');
            if (true !== $validate) {
                return $this->result('',0,$validate);
            }
            $result = model('user/so_user_order','service')->handle($request->post());
            if ($result === false){
                return $this->result('',0,model('user/so_user_order','service')->getError());
            }
            return $this->result($result,200,'处理成功');
        }
    }

    //获取验证码
    public function get_vcode(Request $request){
        if ($request->isAjax()){
            $result = model('message/Sms','service')->send_one($request->post('mobile'), 'SMS_155190221', [], 1);
            if ($result === false){
                return $this->result('',0,'发送失败，请稍后再试');
            }
            return $this->result($result,200,'验证码发送成功');
        }
    }
}