<?php
namespace app\admin\controller;

use think\Request;

class Notify extends Admin
{
	protected function initialize() {
        parent::initialize();
        $this->service = model('message/sms','service');
    }

    //发送短信通知
    public function sms(Request $request)
    {
    	if ($request->isAjax()){
    		$mobile = $request->post('mobile');
            $tpl_id = $request->post('tpl_id');
            $params = $request->post('params');
	    	$list = $this->service->send_all($mobile, $tpl_id, $params);
	    	if ($list->isEmpty()){
                return $this->result('',0,'暂无数据');
            }
	    	//注意：此处状态码一定和前端配置的状态码相同，否则数据会出问题(正常返回200，错误返回0)
	    	return $this->result($list,200);
    	}
        $template = model('message/sms_template','service')->lists(['status'=>1],'create_time DESC',false,false);
        $this->assign('template',$template);
    	return $this->fetch('sms');
    }
}