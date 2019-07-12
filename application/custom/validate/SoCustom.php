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
      'nickname|昵称' => 'require|max:50',
      'email|电子邮箱' => 'email|max:100',
      'password|登陆密码' => 'require|max:32|min:6',
      'account|用户名' => 'require|max:20',
      'status|账户状态' => 'in:-1,1',
      'code|验证码' => 'require|number',
      'old_password|旧密码' => 'require|max:32|min:6',
      'new_password|新密码' => 'require|max:32|min:6',
      're_password|确认密码' => 'require|max:32|min:6',
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
        'add'  => ['username','password','nickname','mobile','email'],
        'edit'  => ['nickname','email'],
        'change_mobile' => ['mobile','code'],
        'change_password' => ['old_password','new_password','re_password'],
        'login' => ['account','password'],
        'set_status' => ['status']
    ];
}