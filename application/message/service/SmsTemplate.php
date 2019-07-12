<?php
namespace app\message\service;

use app\common\library\Service;
use think\Db;

class SmsTemplate extends Service
{
    use \app\common\library\traits\Model;

    // 模型验证
    protected $modelValidate = null;
    // 批量验证
    protected $batchValidate = null;

    protected function _initialize() {
        parent::_initialize();
    }

    /**
     * 短信模板列表
     * @param mixed $maps   查询条件
     * @return mixed
     */
    public function lists($maps = '', $order = 'create_time DESC', $page = 0, $limit = 12, $field = true, $relations = [], $attrs =[]) {
        $model = model('message/sms_template');
        
        if (isset($maps['id'])) {
            $model = $model->where('id','=',$maps['id']);
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
        $model = model('message/sms_template');
        if (!empty($maps['id'])){
            $model = $model->where('id', '=', $maps['id']);
        }

        $result = $model->field($field)->relation($relations)->find();
        if (!$result) {
            $this->error = '短信模板不存在';
            return false;
        }

        /* 获取器数据 */
        if(!empty($attrs)) {
            array_map(function($attr) use (&$result) {
                $attr = trim($attr);
                return $result->$attr = $result->getAttr($attr);
            }, $attrs);
        }
        return $result;
    }

    /**
     * 创建短信模板
     * @param array $data 短信模板信息
     * @return mixed
     */
    public function create($data = []) {
        $model = model('message/sms_template');
        if (empty($data)) {
            $this->error = '模板信息不能为空';
            return false;
        }
        Db::startTrans();
        try{
            $model->isUpdate(false)->save($data);
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
        Db::commit();
        return $model;
    }

    /**
     * 编辑
     * @param array  $data        短信模板信息
     * @param string $id  短信模板id
     * @return mixed
     */
    public function save($data = [], $id = '') {
        $model = model('message/sms_template');
        if (empty($data)) {
            $this->error = '短信模板信息不能为空';
            return false;
        }
        $info = $model->where('id','=',$id)->find();
        if (empty($info->id)) {
            $this->error = '短信模板不存在';
            return false;
        }
        Db::startTrans();
        try {
            $model->isUpdate(true)->save($data);
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
     * @param string $id 短信模板id
     * @return mixed
     */
    public function destroy($id = '') {
        $model = model('message/sms_template');
        if (empty($id)) {
            $this->error = '要删除的短信模板不能为空';
            return false;
        }
        $info = $model->where('id','=',$id)->find();
        if (!$info) {
            $this->error = '要删除的短信模板不存在';
            return false;
        }
        Db::startTrans();
        try {
            $model->destroy($id);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
        Db::commit();
        return $model;
    }
}