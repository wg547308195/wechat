<?php 
namespace app\user\validate;
use think\Validate;

class SoUser extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
      'openid|openid' => 'require|max:64|unique:so_user',
      'custom_id|所属经销商' => 'require|number|max:10',
      'mobile|手机号' => 'require|number|mobile',
      'nickname|昵称' => 'max:50',
      'address|地址' => 'max:255',
      'headimgurl|头像' => 'max:255',
      'province|省份' => 'max:255',
      'city|城市' => 'max:255',
      'sex|性别' => 'number|in:0,1,2',
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
        'add'  => ['openid','nickname','address','headimgurl','province','city','sex'],
        'bind'  => ['openid','custom_id','mobile'],
        'disabled' => ['status'],
    ];
}