<?php
namespace app\admin\controller;

use think\Request;

class Sms extends Admin
{
	protected function initialize() {
        parent::initialize();
        $this->service = model('message/sms_template','service');
    }

    //模板列表
    public function index(Request $request)
    {
    	if ($request->isAjax()){
    		$maps = [];
	    	$list = $this->service->lists($maps,'create_time DESC',$this->page,$this->limit,true);
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
	        $validate = $this->validate($request->post(), 'app\message\validate\SmsTemplate.add');
	        if (true !== $validate) {
	            return $this->result('',0,$validate);
	        }
    		$data = $request->post();
	    	$result = $this->service->create($data);
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
    		$data = $request->post();
            $data['status'] = ($data['status'] == 'true') ? 1 : -1;
	        //验证
	        $validate = $this->validate($data, 'app\message\validate\SmsTemplate.edit');
	        if (true !== $validate) {
	            return $this->result('',0,$validate);
	        }
	    	$result = $this->service->save($data,$request->post('id'));
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
        $result = $this->service->destroy($request->post('id'));
        if ($result === false) {
            return $this->result('',0,$this->service->getError());
        }
        return $this->result($result,200,'删除成功');
    }
}