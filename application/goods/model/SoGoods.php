<?php
namespace app\goods\model;

use app\common\library\Model;

class SoGoods extends Model
{
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

    /**
     * [Category 分类]
     */
    public function Category(){
        return $this->hasOne("app\\goods\\model\\SoCategory",'id', 'cat_id');
    }
}