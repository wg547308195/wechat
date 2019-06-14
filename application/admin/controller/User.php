<?php
namespace app\admin\controller;

use think\Request;

class User extends Admin
{
	protected function initialize() {
        parent::initialize();
        $this->service = model('user/SoUser','service');
    }

    //用户列表
    public function index(Request $request)
    {
    	if ($request->isAjax()){
    		$maps = [];
	    	if ($request->has('nickname','param',true)){
	    		$maps['nickname'] = $request->param('nickname');
	    	}
	    	if ($request->has('mobile','param',true)){
	    		$maps['mobile'] = $request->param('mobile');
	    	}
	    	$list = $this->service->lists($maps,'create_time DESC',$this->page,$this->limit,true);
            if (!empty($list)){
                foreach ($list as $key => $value) {
                    $value->custom;
                }
            }
	    	if ($list->isEmpty()){
                return $this->result('',0,'暂无数据');
            }
	    	//注意：此处状态码一定和前端配置的状态码相同，否则数据会出问题(正常返回200，错误返回0)
	    	return $this->result($list,200);
    	}
    	return $this->fetch('index');
    }
}