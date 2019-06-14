<?php 
namespace app\admin\validate;
use think\Validate;

class SoAdmin extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
      'username|用户名' => 'require|unique:so_admin|max:20',
      'password|登陆密码' => 'require|max:32|min:6',
      'nickname|昵称' => 'max:50',
      'avatar|头像' => 'max:255',
      'email|电子邮箱' => 'email',
      'account|用户名' => 'require|max:20'
    ];

     /**
     * 提示消息
     */
    protected $message = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['username','password','nickname','avatar','email'],
        'edit' => ['nickname','avatar','email'],
        'login' => ['account','password']
    ];
}