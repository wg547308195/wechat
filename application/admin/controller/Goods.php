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
            $list = $this->service->lists($maps,'update_time DESC',$this->page,$this->limit,$field,$relations,$attrs); 
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
            $date = [];
            $date['name'] = (string) $request->post('name');
            $date['price'] = $request->post('price');
            $date['imgs'] =  $request->post('imgs');
            $date['desc'] = $request->post('desc');
            $date['performance'] = $request->post('performance');
            $date['sort'] = (int) $request->post('sort');
            $date['status'] = (int) $request->post('status');

            $validate = new \app\goods\validate\SoGoods;
            if(!$validate->scene('create')->check($date)){
                return $this->result('',0,$validate->getError());
            }
            $result = $this->service->create($date);
            if ($result === false){
                return $this->result('',0,$this->service->getError());
            }
            return $this->result($result,200,'添加成功');
        }
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
            $validate = new \app\goods\validate\SoGoods;
            if(!$validate->scene('edit')->check($data)){
                return $this->result('',0,$validate->getError());
            }
            $result = $this->service->save($data,$data['goods_id']);
            if ($result === false) {
                return $this->result('',0,$this->service->getError());
            }
            return $this->result($result,200,'修改成功');
        }
        return $this->fetch('edit');
    }

    /**
     * 删除产品
     */
    public function delete(Request $request)
    {
        if ($request->isAjax()) {
            $result = $this->service->destroy($request->post('goods_id'));
            if ($result === false) {
                return $this->result('',0,$this->service->getError());
            }
            return $this->result($result,200,'删除成功');
        }
        return $this->fetch('delete'); 
    }

}