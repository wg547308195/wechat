<?php
namespace app\goods\service;

use app\common\library\Service;
use think\Db;

class SoGoods extends Service
{

    // 模型验证
    protected $modelValidate = null;
    // 批量验证
    protected $batchValidate = null;

    protected function _initialize() {
        parent::_initialize();
    }

    /**
     * 产品列表
     * @param mixed $maps   查询条件
     * @return mixed
     */
    public function lists($maps = '', $order = '', $page = 0, $limit = 12, $field = true, $relations = [], $attrs =[]) {
        $model = model('goods/SoGoods');
        if (isset($maps['name'])) {
            $model = $model->where('name','like','%'.$maps['name'].'%');
        }
        if (isset($maps['status'])) {
            $model = $model->where('status','=',$maps['status']);
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
        $model = model('goods/SoGoods');
        if (!empty($maps['id'])){
            $model = $model->where('id', '=', $maps['id']);
        }

        $result = $model->field($field)->relation($relations)->find();
        if (!$result) {
            $this->error = '未找到产品信息';
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
     * @param array $params 产品相关信息
     * @return mixed
     */
    public function create($params = []) {
        $model = model('goods/SoGoods');
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
     * @param int $id 产品id
     * @param array $params 产品相关信息
     * @return mixed
     */
    public function save($params = [], $id = 0) {
        $model = model('goods/SoGoods');
        $info = $model->where('id','=',$id)->find();
        if (empty($info->id)) {
            $this->error = '产品信息未找到';
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
     * @param int $id 产品id
     * @return mixed
     */
    public function destroy($id = 0) {
        $model = model('goods/SoGoods');
        if (empty($id)) {
            $this->error = '要删除的产品信息不能为空';
            return false;
        }
        $info = $model->where('id','=',$id)->find();
        if (!$info) {
            $this->error = '产品信息未找到';
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
}