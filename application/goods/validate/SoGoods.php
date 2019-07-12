<?php 

 namespace app\goods\validate;

 use think\Validate;

 class SoGoods extends Validate
 {
 	/**
 	 * 规则
 	 * @var [type]
 	 */
 	protected $rule = [
 		'id|产品' => 'require|max:30',
 		'name|产品名称' => 'require|max:50',
 		'cat_id|产品分类' => 'require|number|max:10',
 		'img|产品图片' => 'require|max:255',
 		'desc|描述信息' => 'require',
 		'performance|性能指标' => 'require',
 		'time_length|保修期' => 'require|number|max:10',
        'status|状态' => 'require|between:0,1',
 	];

 	/**
 	 * 场景
 	 * @var [type]
 	 */
 	protected $scene =[
 		'add' => ['name','cat_id','img','desc','performance','time_length','status'],
 		'edit' => ['id','name','cat_id','img','desc','performance','status'],
 	];

 }