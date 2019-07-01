<?php
namespace app\admin\controller;

use think\Request;

class Goods extends Admin
{
    protected function initialize() {
        parent::initialize();
        $this->service = model('goods/SoGoods', 'service');
    }

    /**
     * 产品列表
     */
    public function index(Request $request)
    {  
        if ($request->isAjax()){
            $field = $this->request->param('field/s',true);
            $relations = array_filter($this->request->param('relations/a',[]));
            $attrs = array_filter($this->request->param('attrs/a',[]));
            $maps = [];
            if ($request->has('name','param',true)){
                $maps['name'] = $request->param('name');
            }
            if ($request->has('status','param',true)){
                $maps['status'] = $request->param('status');
            }
            $list = $this->service->lists($maps,'create_time DESC',$this->page,$this->limit,$field,$relations,$attrs);
            if (!empty($list)){
                foreach ($list as $key => $value) {
                    $value->category;
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

    /**
     * 添加产品页面
     */
    public function add(Request $request)
    {
        if ($request->isAjax()){
            //验证
            $validate = $this->validate($request->post(), 'app\goods\validate\SoGoods.add');
            if (true !== $validate) {
                return $this->result('',0,$validate);
            }
            $result = $this->service->create($request->post());
            if ($result === false){
                return $this->result('',0,$this->service->getError());
            }
            return $this->result($result,200,'添加成功');
        }
        $category = model('goods/so_category','service')->lists([],'create_time ASC',false,false,true);
        $this->assign('category',$category);
        return $this->fetch('add');
    }

    /**
     * 编辑产品
     */
    public function edit(Request $request)
    {
        if ($request->isAjax()){
            $data = $request->post();
            $data['status'] = ($data['status'] == 'true') ? 1 : 0;
            //验证
            $validate = $this->validate($data, 'app\goods\validate\SoGoods.edit');
            if (true !== $validate) {
                return $this->result('',0,$validate);
            }
            $result = $this->service->save($data,$data['goods_id']);
            if ($result === false) {
                return $this->result('',0,$this->service->getError());
            }
            return $this->result($result,200,'修改成功');
        }
        $category = model('goods/so_category','service')->lists([],'create_time ASC',false,false,true);
        $this->assign('category',$category);
        return $this->fetch('edit');
    }

    /**
     * 删除产品
     */
    public function delete(Request $request)
    {
        $result = $this->service->destroy($request->post('goods_id'));
        if ($result === false) {
            return $this->result('',0,$this->service->getError());
        }
        return $this->result($result,200,'删除成功');
    }

}