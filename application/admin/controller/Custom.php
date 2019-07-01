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
	    	if (!empty($list)){
                foreach ($list as $key => $value) {
                    $value->area;
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

    //设置经销商状态
    public function set_status(Request $request)
    {
        //验证
        $validate = $this->validate($request->post(), 'app\custom\validate\SoCustom.set_status');
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
     * 删除经销商
     */
    public function delete(Request $request)
    {
        $result = $this->service->destroy($request->post('id'));
        if ($result === false) {
            return $this->result('',0,$this->service->getError());
        }
        return $this->result($result,200,'删除成功');
    }

    /**
     * ajax获取地区数据
     */
    public function get_area_children(Request $request){
        $parent_id = $request->param('parent_id',100000);
        $list = model('setting/SysArea','service')->get_children($parent_id);
        if ($list === false){
            return $this->result('',0,'暂无数据');
        }
        //注意：此处状态码一定和前端配置的状态码相同，否则数据会出问题(正常返回200，错误返回0)
        return $this->result($list,200);
    }
}