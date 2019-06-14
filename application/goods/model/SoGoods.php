<?php
namespace app\goods\model;

use app\common\library\Model;

class SoGoods extends Model
{
    protected $pk = 'goods_id';
    protected $append = [
    	'status_text','desc'
    ];

    protected function getStatusTextAttr($value,$data)
    {
    	$type = ['0' => '下架', '1' => '上架'];
    	return $type[$data['status']];

    }

    public function getContentAttr($value,$data){
        return htmlspecialchars_decode(htmlspecialchars_decode($data['desc']));
    }
}