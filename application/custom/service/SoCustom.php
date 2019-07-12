<?php
namespace app\custom\service;

use app\common\library\Service;
use think\Db;

class SoCustom extends Service
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
     * 经销商列表
     * @param mixed $maps   查询条件
     * @return mixed
     */
    public function lists($maps = '', $order = 'create_time DESC', $page = 0, $limit = 12, $field = true, $relations = [], $attrs =[]) {
        $model = model('custom/so_custom');
        
        if (isset($maps['id'])) {
            $model = $model->where('id','=',$maps['id']);
        }
        if (!empty($maps['username'])){
            $model = $model->where('username','like','%'.$maps['username'].'%');
        }
        if (!empty($maps['mobile'])){
            $model = $model->where('mobile','like','%'.$maps['mobile'].'%');
        }
        if (!empty($maps['email'])){
            $model = $model->where('email','like','%'.$maps['email'].'%');
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
        $model = model('custom/so_custom');
        if (!empty($maps['id'])){
            $model = $model->where('id', '=', $maps['id']);
        }
        if (!empty($maps['mobile'])) {
            $model = $model->where('mobile', '=', $maps['mobile']);
        }

        $result = $model->field($field)->relation($relations)->find();
        if (!$result) {
            $this->error = '经销商不存在';
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
     * 创建经销商
     * @param array $data 经销商信息
     * @return mixed
     */
    public function create($data = []) {
        $model = model('custom/so_custom');
        if (empty($data)) {
            $this->error = '经销商信息不能为空';
            return false;
        }
        $info = $model->where('mobile','=',$data['mobile'])->find();
        if (!empty($info->id)){
            $this->error = '手机号已存在';
            return false;
        }
        $data['salt'] = \fast\Random::nozero(6);
        $data['password'] = md5(md5($data['password']).$data['salt']);
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
     * @param array  $data        经销商信息
     * @param string $id  经销商id
     * @return mixed
     */
    public function save($data = [], $id = '') {
        $model = model('custom/so_custom');
        if (empty($data)) {
            $this->error = '经销商信息不能为空';
            return false;
        }
        $custom = $model->where('id','=',$id)->find();
        if (empty($custom->id)) {
            $this->error = '经销商不存在';
            return false;
        }
        if (isset($data['mobile'])){
            $info = $model->where('id','neq',$id)->where('mobile','=',$data['mobile'])->find();
            if (!empty($info->id)){
                $this->error = '手机号已存在';
                return false;
            }
        }
        if (isset($data['password'])){
            if ($custom->password === $data['password']){
                unset($data['password']);
            }else{
                $data['password'] = md5(md5($data['password']).$custom->salt);
            }
        }
        Db::startTrans();
        try {
            $custom->isUpdate(true)->save($data);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
        Db::commit();
        return $custom;
    }

    /**
     * 软删除
     * @param string $id 经销商id
     * @return mixed
     */
    public function destroy($id = '') {
        $model = model('custom/so_custom');
        if (empty($id)) {
            $this->error = '要删除的经销商不能为空';
            return false;
        }
        $info = $model->where('id','=',$id)->find();
        if (!$info) {
            $this->error = '要删除的经销商不存在';
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

    /**
     * 状态修改
     * @param [array] $data [信息]
     * return mix
     */
    public function set_status($data = []){
        $model = model('custom/so_custom');
        \Db::startTrans();
        try{
            $result = $model->getOrFail($data['id']);
        }catch (\Exception $e) {
            $this->error = '信息不存在';
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

    /**
     * 登录
     */
    public function login($params = []){
        $model = model('custom/so_custom');
        Db::startTrans();

        $custom = $model->where('username' ,'=' ,$params['username'])->find();
        if(empty($custom->id)) {
            $this->error = '用户名或密码错误';
            return false;
        }
        if($custom['password'] !== md5(md5($params['password']) . $custom['salt'])){
            $this->error = '用户名或密码错误';
            return false;
        }
        //写入登陆cookie
        cookie('custom_token',encrypt($custom->username."_\t".$custom->password));
        Db::commit();
        return $custom;
    }

    /**
     * 登录验证
     * @param string  $account_token  用户token
     * @return mixed
     */
    public function validate_token($custom_token = ''){
        if (empty($custom_token)){
            $this->error = 'token不能为空';
            return false;
        }
        $custom_token = decrypt($custom_token);
        list($username, $password) = explode("_\t", $custom_token);
        $custom = model('custom/so_custom')->where('username','=',$username)->where('password','=',$password)->find();
        if (empty($custom->id)){
            $this->error = '非法登陆';
            return false;
        }
        return $custom;
    }
}