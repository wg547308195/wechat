<?php

namespace app\custom\model;

use app\common\library\Model;

class SoCustom extends Model
{
	/**
     * [Area 地区]
     */
    public function Area(){
        return $this->hasOne("app\\setting\\model\\SysArea",'id', 'area_id');
    }
}
