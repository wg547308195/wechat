<?php
namespace app\admin\controller;

use think\Request;

class Setting extends Admin
{
    protected function initialize() {
        parent::initialize();
        $this->service = model('setting/SysSetting','service');
    }

    /**
     * 网站设置
     */
    public function index(Request $request){
        $info = $this->service->info();
        $this->assign('info',$info);
        return $this->fetch('index');
    }

    /**
     * 微信设置
     */
    public function wechat(Request $request){
        $info = $this->service->info();
        $this->assign('info',$info);
        return $this->fetch('wechat');
    }

    /**
     * 短信设置
     */
    public function sms(Request $request){
        $info = $this->service->info();
        $this->assign('info',$info);
        return $this->fetch('sms');
    }

    /**
     * ajax配置方法
     */
    public function setting(Request $request){
        $data = $request->post();
        $result = $this->service->setting($data);
        if ($result === false){
            return $this->result('',0,$this->service->getError());
        }
        return $this->result($result,200,'设置成功');
    }
}