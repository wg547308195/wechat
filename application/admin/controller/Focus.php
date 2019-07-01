<?php
namespace app\admin\controller;

use think\Request;

class Focus extends Admin
{
	protected function initialize() {
        parent::initialize();
        $this->service = model('setting/sys_focus','service');
    }

    //列表
    public function index(Request $request)
    {
    	if ($request->isAjax()){
    		$maps = [];
	    	$list = $this->service->lists($maps,'create_time ASC',$this->page,$this->limit,true);
	    	if ($list->isEmpty()){
                return $this->result('',0,'暂无数据');
            }
	    	return $this->result($list,200);
    	}
    	return $this->fetch('index');
    }

    //添加
    public function add(Request $request)
    {
    	if ($request->isAjax()){
	        //验证
	        $validate = $this->validate($request->post(), 'app\setting\validate\SysFocus.add');
	        if (true !== $validate) {
	            return $this->result('',0,$validate);
	        }
	    	$result = $this->service->create($request->post());
	    	if ($result === false){
	    		return $this->result('',0,$this->service->getError());
	    	}
	    	return $this->result($result,200,'添加成功');
    	}
    	return $this->fetch('add');
    }

    //编辑
    public function edit(Request $request)
    {
    	if ($request->isAjax()){
	        //验证
	        $validate = $this->validate($request->post(), 'app\setting\validate\SysFocus.edit');
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

    //设置状态
    public function set_status(Request $request)
    {
        //验证
        $validate = $this->validate($request->post(), 'app\setting\validate\SysFocus.set_status');
        if (true !== $validate) {
            return $this->result('',0,$validate);
        }
        $result = $this->service->set_status($request->post());
        if ($result === false){
            return $this->result('',0,$this->service->getError());
        }
        return $this->result($result,200,'操作成功');
    }

    /**
     * 删除
     */
    public function delete(Request $request)
    {
        $result = $this->service->destroy($request->post('id'));
        if ($result === false) {
            return $this->result('',0,$this->service->getError());
        }
        return $this->result($result,200,'删除成功');
    }
}