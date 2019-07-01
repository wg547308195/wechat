<?php 

 namespace app\setting\validate;

 use think\Validate;

 class SysFocus extends Validate
 {
 	/**
 	 * 规则
 	 * @var [type]
 	 */
 	protected $rule = [
 		'name|名称' => 'require|max:50',
 		'img|图片地址' => 'require|max:255',
 		'link|链接地址' => ['regex'=>'/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/'],
 		'sort|排序' => 'number|max:10',
 		'status|状态' => 'require|in:-1,1'
 	];

 	/**
 	 * 场景
 	 * @var [type]
 	 */
 	protected $scene =[
 		'add' => ['name','img','link','sort'],
 		'edit' => ['name','img','link','sort'],
 		'set_status' => ['status']
 	];

 }