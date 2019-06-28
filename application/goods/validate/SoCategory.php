<?php 

 namespace app\goods\validate;

 use think\Validate;

 class SoCategory extends Validate
 {
 	/**
 	 * 规则
 	 * @var [type]
 	 */
 	protected $rule = [
 		'pid|父级分类' => 'require|max:10|number',
 		'name|分类名称' => 'require|max:50',
 		'sort|排序' => 'number|max:10'
 	];

 	/**
 	 * 场景
 	 * @var [type]
 	 */
 	protected $scene =[
 		'add' => ['name'],
 		'edit' => ['name']
 	];

 }