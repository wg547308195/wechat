<?php
namespace app\admin\service;

use app\common\library\Service;
use think\Db;

class SoAdmin extends Service
{
	use \app\common\library\traits\Model;

	// 模型验证
	protected $modelValidate = null;
	// 批量验证
	protected $batchValidate = null;

	public function _initialize() {
		parent::_initialize();
	}

	/**
	 * 列表数据查询
	 * @param mixed $maps   查询条件
	 * @param int $limit    返回数量
	 * @param int $page     当前分页
	 * @param string $order 排序方式
	 * @param bool $field   指定字段
	 * @param array $extra  关联查询
	 * @param array $chains 链式操作
	 * @return mixed
	 */
	public function lists($maps = '', $limit = 12, $page = 1, $order = 'id DESC', $field = true, $relations = [], $funcs = [],$attrs = []) {
		$model = model('admin/so_admin');

		$model = $model->where($maps)->order($order)->field($field);

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

		// 获取附加获取器
		if (is_array($attrs) && !empty($attrs)) {
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
	 * 添加
	 */
	public function create($data = []){
		$model = model('admin/so_admin');
		Db::startTrans();
		try {
			//生成token
			$data['token'] = \fast\Random::uuid();
			$data['salt'] = \fast\Random::nozero(6);
			$data['password'] = md5(md5($data['password']) . $data['salt']);
			// //用户所属分组
			// if (!empty($data['group_id'])){
			// 	$group = model('admin/so_auth_group')->where('id','=',$data['group_id'])->find();
			// 	if (empty($group->id)){
			// 		$this->error = '未找到分组信息';
			// 		return false;
			// 	}
			// 	$data['group_name'] = $group['name'];
			// }
			//验证
			$validate = new \app\admin\validate\SoAdmin;
			if (!$validate->scene('add')->check($data)){
				\Db::rollback();
				$this->error = $validate->getError();
				return false;
			}
			//创建用户
			$model->isUpdate(false)->save($data);

		} catch (\Exception $e) {
			Db::rollback();
        	\Log::write('创建失败：'.$e);
            $this->error = $e->getMessage();
            return false;
		}

		Db::commit();
		return $model;
	}

	/**
	 * 修改
	 * @param [array] $data [信息]
	 * return mix
	 */
	public function save($data = []){
		$model = model('admin/so_admin');
		Db::startTrans();
        try {
            $result = $model->getOrFail($data['id']);
        } catch (\Exception $e) {
            $this->error = '信息不存在';
            return false;
        }
        try{
        	if(!empty($data['password'])){
				$data['salt'] = \fast\Random::nozero(6);
				$data['password'] = md5(md5($data['password']) . $data['salt']);
			}
			// //用户所属分组
			// if (!empty($data['group_id'])){
			// 	$group = model('admin/so_auth_group')->where('id','=',$data['group_id'])->find();
			// 	if (empty($group->id)){
			// 		$this->error = '未找到分组信息';
			// 		return false;
			// 	}
			// 	$data['group_name'] = $group['name'];
			// }
        	$validate = new \app\admin\validate\SoAdmin;
			if (!$validate->scene('edit')->check($data)){
				Db::rollback();
				$this->error = $validate->getError();
				return false;
			}
			$result->isUpdate(true)->save($data);
        } catch (\Exception $e) {
            Db::rollback();
            \Log::write('编辑失败：'.$e);
            $this->error = $e->getMessage();
            return false;
        }

		Db::commit();
		return $result;
	}

	/**
	 * 登录
	 */
	public function login($params = []){
		$model = model('admin/so_admin');
		Db::startTrans();

		$admin = $model->where('username' ,'=' ,$params['username'])->find();
		if(!$admin) {
            $this->error = '用户名或密码错误';
            return false;
        }
        if($admin['password'] !== md5(md5($params['password']) . $admin['salt'])){
            $this->error = '用户名或密码错误';
            return false;
        }

		$data_logs['admin_id'] = $admin->id;
		$data_logs['ip'] = request()->ip();
		$data_logs['user_agent'] = request()->server('HTTP_USER_AGENT');
		//写入登陆cookie
        cookie('account_token',encrypt($admin->username."_\t".$admin->password));
		//日志
		$ret_log = model('admin/so_admin_logs')->save($data_logs);
		if (!$ret_log){
			$this->error = '日志写入失败';
            return false;
		}
		Db::commit();
		return $admin;
	}

	/**
     * 登录验证
     * @param string  $account_token  用户token
     * @return mixed
     */
    public function validate_token($account_token = ''){
        if (empty($account_token)){
            $this->error = 'token不能为空';
            return false;
        }
        $account_token = decrypt($account_token);
        list($username, $password) = explode("_\t", $account_token);
        $admin = model('admin/so_admin')->where('username','=',$username)->where('password','=',$password)->find();
        if (empty($admin->id)){
            $this->error = '非法登陆';
            return false;
        }
        return $admin;

    }

	/**
	 * 删除
	 * @param [int] $id [主键id]
	 * return mix
	 */
	public function delete($id = 0){
		$model = model('admin/so_admin');

		Db::startTrans();
        try {
        	if ($id == 1){
				$this->error = '超级管理员禁止删除';
	            return false;
			}
            $result = $model->getOrFail($id);
        } catch (\Exception $e) {
            $this->error = '信息不存在';
            return false;
        }
		try {
			$result->destroy($id);
		} catch (\Exception $e) {
			Db::rollback();
			\Log::write('删除失败：'.$e);
            $this->error = $e->getMessage();
            return false;
		}
		Db::commit();
		return $result;
	}

	/**
     * 状态修改
     * @param [array] $data [信息]
     * return mix
     */
    public function set_status($data = []){
        $model = model('admin/so_admin');
        if (!in_array($data['status'],[-1,1])){
        	$this->error = '状态非法';
            return false;
        }
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
	 * 获取信息
	 */
	public function getInfo($value,$field){
		$result = model('admin/so_admin')->cache('user_'.$field.'_'.$value,'1800')->where([$field => $value])->find();
		if(empty($result)){
			return "";
		}
		return $result;
	}

}
