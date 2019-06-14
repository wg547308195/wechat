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
 		'goods_id|产品信息' => 'require|max:25',
 		'name|产品名称' => 'require|max:50',
 		'price|产品报价' => 'require',
 		'imgs|产品图片' => 'require',
 		'desc|描述信息' => 'require',
 		'performance|性能指标' => 'require',
        'status|状态' => 'require|between:0,1',
 	];

 	/**
 	 * 场景
 	 * @var [type]
 	 */
 	protected $scene =[
 		'create' => ['name','imgs','desc','price','performance','status'],
 		'edit' => ['goods_id','name','imgs','desc','price','performance','status'],
 	];

 }