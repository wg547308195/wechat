<?php
namespace app\attachment\validate;

use think\Validate;

class Attachment extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'org_code|党组织层级码'        => 'require|max:50',
        'org_zb_code|党组织识别码'     => 'require|max:12',
        'relation_table|关联表'     => 'require|max:50',
        'name'     => 'require|max:50',
        'relation_id|关联表id'     => 'require',
        'file_type'     => 'require',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['org_code','org_zb_code','relation_table','relation_id','file_type','name'],
        'edit'  => ['org_code','org_zb_code','relation_table','relation_id','file_type','name']
    ];

}