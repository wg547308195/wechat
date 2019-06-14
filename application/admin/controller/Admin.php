<?php
namespace app\admin\controller;

use think\Controller;
use think\facade\Request;

class Admin extends Controller
{
    protected $page;
    protected $limit;

    protected function initialize()
    {
        $this->page = Request::get('page',0);
        $this->limit = Request::get('limit',12);
        // 判断用户是否登陆
        $this->admin = model('admin/so_admin','service')->validate_token(cookie('account_token'));
        if (empty($this->admin['id']) || $this->admin['status'] == -1){
            $this->redirect('login/index');
        }
        // 获取节点树
        $nodes = model('admin/so_admin_node','service')->node_tree();
        $this->assign('nodes',$nodes);
        $this->assign('admin',$this->admin);
    }
}