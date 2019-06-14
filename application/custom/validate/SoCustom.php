<?php 
namespace app\custom\validate;
use think\Validate;

class SoCustom extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
      'username|用户名' => 'require|max:20|unique:so_custom',
      'mobile|手机号' => 'require|mobile',
      'nickname|昵称' => 'max:50',
      'email|电子邮箱' => 'email|max:100',
      'status|账户状态' => 'number|in:-1,1'
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
        'add'  => ['username','nickname','mobile','email'],
        'edit'  => ['nickname','mobile','email'],
        'disabled' => ['status'],
    ];
}