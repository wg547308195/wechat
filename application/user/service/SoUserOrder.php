<?php
namespace app\user\service;

use app\common\library\Service;
use think\Db;

class SoUserOrder extends Service
{

    // 模型验证
    protected $modelValidate = null;
    // 批量验证
    protected $batchValidate = null;

    protected function _initialize() {
        parent::_initialize();
    }

    /**
     * 维保单列表
     * @param mixed $maps   查询条件
     * @return mixed
     */
    public function lists($maps = '', $order = '', $page = 0, $limit = 12, $field = true, $relations = [], $attrs =[]) {
        $model = model('user/SoUserOrder');
        if (!empty($maps['uid'])) {
            $model = $model->where('uid','=',$maps['uid']);
        }
        if (!empty($maps['user_goods_id'])) {
            $model = $model->where('user_goods_id','=',$maps['user_goods_id']);
        }
        if (isset($maps['status'])) {
            $model = $model->where('status','=',$maps['status']);
        }
        if (!empty($maps['custom_id']) && empty($maps['uid'])){
            $user_ids = model('user/so_user')->where('custom_id','=',$maps['custom_id'])->column('id');
            if (!empty($user_ids)){
                $model = $model->where('uid','in',$user_ids);
            }
        }
        $model = $model->order($order)->field($field);

        if($page !== false) {
            $result = $model->paginate($limit, '', ['page' => $page]);
        } else {
            $result = $model->limit($limit)->select();
        }

        /* 关联数据获取 */
        if(!empty($relations)) {
            foreach ($result as $key => $value) {
                array_map(function($e) use (&$value) {
                    $e = trim($e);
                    $value->$e = $value->$e ?: new \stdClass(); // 注意没有数据不要返回默认的 NULL
                }, $relations);
            }
        }

        /* 获取器数据 */
        if(!empty($attrs)) {
            foreach ($result as $key => $value) {
                array_map(function($attr) use (&$value) {
                    $attr = trim($attr);
                    return $value->$attr = $value->getAttr($attr);
                }, $attrs);
            }
        }
        return $result;
    }

    /**
     * 详情
     * @param [array]   $maps       [查询条件]
     * @param [string]  $field      [查询字段]
     * @param [array]   $relations  [关联数据]
     * @param [array]   $attrs      [获取器数据]
     * return mix
     */
    public function detail($maps = '',$field = true,$relations = [],$attrs = []){
        $model = model('user/SoUserOrder');
        if (!empty($maps['id'])){
            $model = $model->where('id', '=', $maps['id']);
        }

        $result = $model->field($field)->relation($relations)->find();
        if (!$result) {
            $this->error = '未找到维保单信息';
            return false;
        }

        /* 获取器数据 */
        if(!empty($attrs)) {
            array_map(function($attr) use (&$result) {
                $attr = trim($attr);
                return $result->$attr = $result->getAttr($attr);
            }, $attrs);
        }
        Db::commit();
        return $result;
    }

    /**
     * 添加
     * @param array $params 维保单相关信息
     * @return mixed
     */
    public function create($params = []) {
        $model = model('user/SoUserOrder');
        if (empty($params)){
            $this->error = '维保单信息不能为空';
            return false;
        }
        $user_goods = model('user/SoUserGoods','service')->detail(['id'=>$params['user_goods_id']]);
        if (empty($user_goods)){
            $this->error = '要维保的产品不存在';
            return false;
        }
        if (strtotime($user_goods['end_time']) < time()){
            $this->error = '该产品已过保修期';
            return false;
        }
        $user_order = $model->where('uid','=',$params['uid'])->where('user_goods_id','=',$params['user_goods_id'])->where('status','=',0)->find();
        if (!empty($user_order->id)){
            $this->error = '该产品正在维保中';
            return false;
        }
        Db::startTrans();
        try{
            $model->isUpdate(false)->save($params);
        } catch (\Exception $e) {
            \Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
        Db::commit();
        return $model;
    }

    /**
     * 编辑
     * @param int $id 维保单id
     * @param array $params 维保单相关信息
     * @return mixed
     */
    public function save($params = [], $id = '') {
        $model = model('user/SoUserOrder');
        $info = $model->where('id','=',$id)->find();
        if (empty($info->id)) {
            $this->error = '维保单信息未找到';
            return false;
        }
        Db::startTrans();
        try {
            $model->isUpdate(true)->save($params);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
        Db::commit();
        return $model;
    }

    /**
     * 软删除
     * @param int $id 维保单id
     * @return mixed
     */
    public function destroy($id = '') {
        $model = model('user/SoUserOrder');
        if (empty($id)) {
            $this->error = '要删除的维保单信息不能为空';
            return false;
        }
        $info = $model->where('id','=',$id)->find();
        if (empty($info->id)) {
            $this->error = '维保单信息未找到';
            return false;
        }
        Db::startTrans();
        try {
            $model->destroy(['id' => $id]);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
        Db::commit();
        return $info;
    }

    /**
     * 处理维保单
     * @param [array] $data [信息]
     * return mix
     */
    public function handle($data = []){
        $model = model('user/SoUserOrder');
        \Db::startTrans();
        try{
            $result = $model->getOrFail($data['id']);
        }catch (\Exception $e) {
            $this->error = '信息不存在';
            return false;
        }
        if ($data['status'] == -1 && empty($data['remark'])){
            $this->error = '请填写审核备注';
            return false;
        }
        if ($result->status == 1){
            $this->error = '该维保单已处理';
            return false;
        }
        try{
            $result->isUpdate(true)->save($data);
        } catch (\Exception $e) {
            \Db::rollback();
            \Log::write('状态修改失败：'.$e);
            $this->error = $e->getMessage();
            return false;
        }
        \Db::commit();
        return $result;
    }
}