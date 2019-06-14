<?php
namespace app\admin\service;

use app\common\library\Service;
use think\Db;

class SoAdminNode extends Service
{
    // 模型验证
    protected $modelValidate = null;
    // 批量验证
    protected $batchValidate = null;

    protected function _initialize() {
        parent::_initialize();
    }

    /*
    * 获取节点树
    */
    public function node_tree(){
        $result = Db::name('so_admin_node')->order('sort ASC')->where('status','=',1)->select();
        $result = \fast\ArrayHelper::list_to_tree($result);
        return $result;
    }
}