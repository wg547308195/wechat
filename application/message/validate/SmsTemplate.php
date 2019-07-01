<?php 
namespace app\message\validate;
use think\Validate;

class SmsTemplate extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
      'template_id|模板id' => 'require|max:20|unique:sms_template',
      'content|内容' => 'require|max:255',
      'scene|场景' => 'require|max:50',
      'status|状态 ' => 'in:-1,1'
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
        'add'  => ['template_id','content','scene'],
        'edit'  => ['template_id','content','scene'],
        'set_status' => ['status'],
    ];
}