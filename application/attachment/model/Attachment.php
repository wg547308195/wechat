<?php
namespace app\attachment\model;

use app\common\library\Model;

class Attachment extends Model
{

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected $readonly = [];
    protected $table = 'sys_attachment';

}
