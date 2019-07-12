<?php
namespace app\user\controller;

use think\Controller;
use think\Request;

class Init extends Controller
{
    protected $user;

    protected function initialize()
    {
        $this->user = model('user/so_user', 'service')->init();

        cookie('redirect_url', ($_SERVER['REQUEST_URI'] == '' || $_SERVER['REQUEST_URI'] == '/') ? url('user/my/index','','',true) : 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

        if ($this->user['id'] < 1) {
            $this->redirect(url('user/login/login'));
        }

        if ($this->user['custom_id'] < 1) {
            $this->redirect(url('user/login/register',['id'=>$this->user['id']]));
        }

        $this->assign('user', $this->user);
    }

}