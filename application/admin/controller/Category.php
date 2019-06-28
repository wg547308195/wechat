<?php
namespace app\admin\controller;

use think\Request;

class Category extends Admin
{
	protected function initialize() {
        parent::initialize();
        $this->service = model('goods/so_category','service');
    }

    //模板列表
    public function index(Request $request)
    {
    	if ($request->isAjax()){
    		$maps = [];
	    	$list = $this->service->lists($maps,'create_time ASC',$this->page,$this->limit,true);
	    	if ($list->isEmpty()){
                return $this->result('',0,'暂无数据');
            }
	    	//注意：此处状态码一定和前端配置的状态码相同，否则数据会出问题(正常返回200，错误返回0)
	    	return $this->result($list,200);
    	}
    	return $this->fetch('index');
    }

    //添加模板
    public function add(Request $request)
    {
    	if ($request->isAjax()){
	        //验证
	        $validate = $this->validate($request->post(), 'app\goods\validate\SoCategory.add');
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

    //编辑模板
    public function edit(Request $request)
    {
    	if ($request->isAjax()){
	        //验证
	        $validate = $this->validate($request->post(), 'app\goods\validate\SoCategory.edit');
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

    /**
     * 删除模板
     */
    public function delete(Request $request)
    {
        if ($request->isAjax()) {
            $result = $this->service->destroy($request->post('id'));
            if ($result === false) {
                return $this->result('',0,$this->service->getError());
            }
            return $this->result($result,200,'删除成功');
        }
        return $this->fetch('delete'); 
    }
}