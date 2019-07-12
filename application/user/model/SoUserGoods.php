<?php

namespace app\user\model;

use app\common\library\Model;

class SoUserGoods extends Model
{
	protected $append = ['surplus_time'];

	protected $type = [
        'end_time' => 'timestamp:Y-m-d H:i:s'
    ];

	/**
	 * [User 用户]
	 */
	protected function User(){
		return $this->hasOne("app\\user\\model\\SoUser",'id', 'uid');
	}

	/**
	 * [Goods 产品]
	 */
	protected function Goods(){
		return $this->hasOne("app\\goods\\model\\SoGoods",'id', 'goods_id');
	}

	public function getSurplusTimeAttr($value,$data){
		return max(0,floor(($data['end_time']-time())/(24*60*60)));
	}
}
