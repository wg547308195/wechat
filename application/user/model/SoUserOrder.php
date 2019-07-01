<?php

namespace app\user\model;

use app\common\library\Model;

class SoUserOrder extends Model
{
	/**
	 * [User 用户]
	 */
	protected function User(){
		return $this->hasOne("app\\user\\model\\SoUser",'id', 'uid');
	}

	/**
	 * [Goods 产品]
	 */
	protected function User(){
		return $this->hasOne("app\\goods\\model\\SoGoods",'goods_id', 'goods_id');
	}
}
