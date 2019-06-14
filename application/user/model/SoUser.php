<?php

namespace app\user\model;

use app\common\library\Model;

class SoUser extends Model
{
	/**
	 * [Custom 所属经销商]
	 */
	protected function Custom(){
		return $this->hasOne("app\\custom\\model\\SoCustom",'id', 'custom_id');
	}
}
