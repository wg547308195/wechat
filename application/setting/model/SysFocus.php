<?php

namespace app\setting\model;

use app\common\library\Model;

class SysFocus extends Model
{
  	protected $append = ['link'];

  	public function getLinkAttr($value,$data){
  		if (empty($data['link'])){
  			return '';
  		}
		return preg_match("/^http(s)?:\\/\\/.+/", $data['link']) ? $data['link'] : "http://".$data['link'];
	}
}
