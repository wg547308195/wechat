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
	 * [UserGoods 用户产品]
	 */
	protected function UserGoods(){
		return $this->hasOne("app\\user\\model\\SoUserGoods",'goods_id', 'user_goods_id');
	}
}
