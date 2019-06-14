<?php
namespace app\admin\controller;

use think\Request;

class Custom extends Admin
{
	protected function initialize() {
        parent::initialize();
        $this->service = model('custom/SoCustom','service');
    }

    //经销商列表
    public function index(Request $request)
    {
    	if ($request->isAjax()){
    		$maps = [];
	    	if ($request->has('username','param',true)){
	    		$maps['username'] = $request->param('username');
	    	}
	    	if ($request->has('mobile','param',true)){
	    		$maps['mobile'] = $request->param('mobile');
	    	}
	    	if ($request->has('email','param',true)){
	    		$maps['email'] = $request->param('email');
	    	}
	    	$list = $this->service->lists($maps,'create_time DESC',$this->page,$this->limit,true);
	    	if ($list->isEmpty()){
                return $this->result('',0,'暂无数据');
            }
	    	//注意：此处状态码一定和前端配置的状态码相同，否则数据会出问题(正常返回200，错误返回0)
	    	return $this->result($list,200);
    	}
    	return $this->fetch('index');
    }

    //添加经销商
    public function add(Request $request)
    {
    	if ($request->isAjax()){
	        //验证
	        $validate = $this->validate($request->post(), 'app\custom\validate\SoCustom.add');
	        if (true !== $validate) {
	            return $this->result('',0,$validate);
	        }
    		$data = $request->post();
    		$data['admin_id'] = $this->admin->id;
	    	$result = $this->service->create($data);
	    	if ($result === false){
	    		return $this->result('',0,$this->service->getError());
	    	}
	    	return $this->result($result,200,'添加成功');
    	}
    	return $this->fetch('add');
    }

    //编辑经销商
    public function edit(Request $request)
    {
    	if ($request->isAjax()){
	        //验证
	        $validate = $this->validate($request->post(), 'app\custom\validate\SoCustom.edit');
	        if (true !== $validate) {
	            return $this->result('',0,$validate);
	        }
	    	$result = $this->service->save($request->post(),$request->post('id'));
	    	if ($result === false){
	    		return $this->result('',0,$this->service->getError());
	    	}
	    	return $this->result($result,200,'修改成功');
    	}
    	return $this->fetch('edit');
    }
}