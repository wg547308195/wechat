<?php
namespace app\custom\controller;

use think\Controller;
use think\Request;

class Init extends Controller
{
    protected $custom;

    protected function initialize()
    {
        // 判断用户是否登陆
        $this->custom = model('custom/so_custom','service')->validate_token(cookie('custom_token'));
        if (empty($this->custom['id']) || $this->custom['status'] == -1){
            $this->redirect('/custom/login/index');
        }

        $this->assign('custom', $this->custom);
    }

}