<?php 
namespace app\user\validate;
use think\Validate;

class SoUserOrder extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
      'uid|用户' => 'require|number|max:10',
      'goods_id|产品' => 'require|number|max:10',
      'desc|维保描述' => 'require',
      'status|处理状态' => 'in:-1,1',
      'handle_time|处理时间' => 'number|max:10'
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
        'add'  => ['uid','goods_id','desc'],
        'handle'  => ['status','handle_time']
    ];
}