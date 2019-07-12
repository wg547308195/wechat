<?php
namespace app\setting\service;

use app\common\library\Service;

class SysArea extends Service
{
    public function _initialize() {
        parent::_initialize();
        $this->model = model('setting/SysArea');
    }

    /**
     * 列表数据查询
     * @param mixed $maps   查询条件
     * @param int $limit    返回数量
     * @param int $page     当前分页
     * @param string $order 排序方式
     * @param bool $field   指定字段
     * @return mixed
     */
    public function lists($maps = '', $limit = 12, $page = 0, $order = '', $field = true) {
        $result = $this->model->order($order)->field($field)->cache(true);
        if (isset($maps['id']) && $maps['id'] > 0) {
             $result = $result->where('id','=',$maps['id']);
        }
        if (isset($maps['parent_id']) && $maps['parent_id'] > 0) {
             $result = $result->where('parent_id','=',$maps['parent_id']);
        }
        if (isset($maps['level_type'])) {
            $result = $result->where('level_type','=',$maps['level_type']);
        }
        if (isset($maps['name'])) {
            $result = $result->where('name','like','%'.$maps['name'].'%');
        }
        if (isset($maps['zip_code'])) {
            $result = $result->where('zip_code','=',$maps['zip_code']);
        }
        
        if($page !== false) {
            $result = $result->paginate($limit, '', ['page' => $page]);
        } else {
            $result = $result->limit($limit)->select();
        }

        return $result;
    }

    /**
     * 获取指定地区的所有上级地区数组
     * @param int $id 地区主键ID
     * @return array
     */
    public function fetch_parents($id, $isclear = true) 
    {
        static $position;
        if($isclear === true) $position = [];
        $r = $this->model->find($id);
        if($r && $r['parent_id'] > 0) 
        {
            $position[] = $r;
            $this->fetch_parents($r['parent_id'], FALSE);
        }
        return $position;
    }
    /**
     * 返回指定地区下级地区
     * @param int $parent_id
     * @return array
     */
    public function get_children($parent_id = 100000 ,$order = 'id ASC') 
    {
        if((int) $parent_id < 1)
        {
            $this->error = '地区信息有误';
            return false;
        }
        $lists = $this->model->where('parent_id','=',$parent_id)->order($order)->select();
        if($lists === false){
            $this->error = $this->model->getError();
            return false;
        }
        return $lists;
    }
    /**
     * 获取指定地区完整路径
     * @param int $id 地区ID
     * @param string $filed 字段
     * @return array
     */
    public function fetch_position($id = 0, $filed = 'name') {
        $position = $this->fetch_parents($id);
        krsort($position);
        $result = [];
        foreach($position AS $pos) {
            $result[] = $pos[$filed];
        }
        return $result;
    }
    
}