<?php
namespace app\setting\service;

use app\common\library\Service;

class SysSetting extends Service
{
    public function _initialize() {
        parent::_initialize();
    }

    //获取配置
    public function info($key = ''){
        $model = model('setting/SysSetting');
        if (!empty($key)){
            $result = $model->where('key','=',$key)->value('value');
        }else{
            $list = $model->select();
            $result = [];
            if (!empty($list)){
                foreach ($list as $k => $v) {
                  
                    $result[$v->key] = $v->value;
                }
            }
        }
        return $result;
    }
    
    //配置配置项
    public function setting($sets = ''){
        $model = model('setting/SysSetting');
        \Db::startTrans();
        try {
            if (!empty($sets)){
                foreach ($sets as $key => $value) {
                    $r = $model->where('key','=',$key)->find();
                    if (!empty($r)){
                        $model->isUpdate(true)->save(['value'=>$value], ['key' => $key]);
                    }else{
                        $model->isUpdate(false)->save(['key'=>$key,'value'=>$value]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
        \Db::commit();
        return true;
    }
}