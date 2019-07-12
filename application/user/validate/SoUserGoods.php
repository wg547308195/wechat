<?php 
namespace app\user\validate;
use think\Validate;

class SoUserGoods extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
      'uid|用户' => 'require|number|max:10',
      'goods_id|产品' => 'require|number|max:10',
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
        'add'  => ['uid','goods_id']
    ];
}