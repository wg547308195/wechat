<?php
namespace app\user\service;

use app\common\library\Service;
use think\Db;
use EasyWeChat\Factory;

class SoUser extends Service
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
     * 用户列表
     * @param mixed $maps   查询条件
     * @return mixed
     */
    public function lists($maps = '', $order = 'create_time DESC', $page = 0, $limit = 12, $field = true, $relations = [], $attrs =[]) {
        $model = model('user/so_user');
        
        if (isset($maps['id'])) {
            $model = $model->where('id','=',$maps['id']);
        }
        if (isset($maps['custom_id'])) {
            $model = $model->where('custom_id','=',$maps['custom_id']);
        }
        if (!empty($maps['nickname'])){
            $model = $model->where('nickname','like','%'.$maps['nickname'].'%');
        }
        if (!empty($maps['mobile'])){
            $model = $model->where('mobile','like','%'.$maps['mobile'].'%');
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
        $model = model('user/so_user');
        if (!empty($maps['id'])){
            $model = $model->where('id', '=', $maps['id']);
        }
        if (!empty($maps['mobile'])) {
            $model = $model->where('mobile', '=', $maps['mobile']);
        }

        $result = $model->field($field)->relation($relations)->find();
        if (!$result) {
            $this->error = '用户不存在';
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
     * 创建用户
     * @param array $data 用户信息
     * @return mixed
     */
    public function create($data = []) {
        $model = model('user/so_user');
        if (empty($data)) {
            $this->error = '用户信息不能为空';
            return false;
        }
        $user = $model->where('openid','=',$data['openid'])->find();
        if (!empty($user->id)){
            $this->error = '用户已存在';
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
     * @param array  $data        用户信息
     * @param string $id  用户id
     * @return mixed
     */
    public function save($data = [], $id = '') {
        $model = model('user/so_user');
        if (empty($data)) {
            $this->error = '用户信息不能为空';
            return false;
        }
        $user = $model->where('id','=',$id)->find();
        if (empty($user->id)) {
            $this->error = '用户不存在';
            return false;
        }
        Db::startTrans();
        try {
            $user->isUpdate(true)->save($data);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
        Db::commit();
        return $user;
    }

    /**
     * 用户注册
     * @param string  $params[openid]    用户openid
     * @param string  $params[custom_id] 经销商id
     * @param string  $params[mobile]    手机号码
     * @param string  $params[realname]  真实姓名
     * @param string  $params[address]   所在地址
     * @return mixed
     */
    public function register($params = []) {
        $model = model('user/so_user');
        $user = $model->where('openid','=',$params['openid'])->find();
        if (empty($user->id)) {
            $this->error = '用户不存在';
            return false;
        }
        $custom = model('custom/so_custom')->where('id','=',$params['custom_id'])->find();
        if (empty($custom->id)){
            $this->error = '经销商不存在';
            return false;
        }
        $info = $model->where('id','neq',$user->id)->where('mobile','=',$params['mobile'])->find();
        if (!empty($info->id)){
            $this->error = '手机号已被使用';
            return false;
        }
        $data = [];
        $data['custom_id'] = $params['custom_id'];
        $data['mobile'] = $params['mobile'];
        $data['realname'] = $params['realname'];
        $data['address'] = $params['address'];

        Db::startTrans();
        try {
            $model->isUpdate(true)->save($data,['id'=>$user->id]);
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
     * @param string $id 用户id
     * @return mixed
     */
    public function destroy($id = '') {
        $model = model('user/so_user');
        if (empty($id)) {
            $this->error = '要删除的用户不能为空';
            return false;
        }
        $info = $model->where('id','=',$id)->find();
        if (!$info) {
            $this->error = '要删除的用户不存在';
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
     * 实例化用户
     * @return array
     */
    public function init() {
        $user = [
            'id'         => 0,
            'openid'     => '',
            'custom_id'  => 0,
            'nickname'   => '',
            'mobile'     => '',
            'headimgurl' => '',
            'province'   => '',
            'city'       => '',
            'sex'        => '',
            'unionid'    => '',
            'status'     => 0,
            'create_time'  => 0
        ];
        $user_auth = cookie('user_auth');
        if ($user_auth) {
            $user_auth = json_decode(decrypt($user_auth), true);
            $user = model('user/so_user')->where('id','=',$user_auth['id'])->relation(['custom'])->find();
        }
        // $user = model('user/so_user')->where('id','=',20)->relation(['custom'])->find();
        return $user;
    }

    /**
     * 用户注册&登录
     * @return $user
     */
    public function login(){
        $model = model('user/so_user');

        $config = model('setting/SysSetting','service')->info();
        $options = [
            'app_id' => $config['wechat_app_id'],
            'secret' => $config['wechat_secret'],
            'token' => $config['wechat_token']
        ];
        $app = Factory::officialAccount($options);
        $userinfo = $app->oauth->user();
        $userinfo = $userinfo->toArray();

        $user = $model->where('openid','=',$userinfo['id'])->find();
        if(empty($user->id)) {
            $data = [];
            $data['nickname'] = $userinfo['nickname'];
            $data['openid']   = $userinfo['id'];
            $user = $this->create($data);
            if (!$user){
                $this->error = $this->error;
                return false;
            }
        }

        $auth = [];
        $auth['id'] = $user->id;
        $auth['openid'] = $userinfo['id'];
        cookie('user_auth', encrypt(json_encode($auth)));

        return $user;
    }
}