<?php
/**
 * Created by PhpStorm.
 * User: xuewl
 * Date: 2018/1/2
 * Time: 13:40
 */
return [
    // 缓存配置为复合类型
    'type'  =>  'complex',
    'default'	=>	[
        'type'	=>	'file',
        // 全局缓存有效期（0为永久有效）
        'expire'=>  0,
        // 缓存前缀
        'prefix'=>  '',
        // 缓存目录
        'path'  =>  \think\facade\Env::get('runtime_path') . 'cache/',
    ],
    'redis'	=>	[
        'type'	=>	'redis',
        'host'	=>	\think\facade\Env::get('redis.host', 'redis'),
        // 全局缓存有效期（0为永久有效）
        'expire'=>  \think\facade\Env::get('redis.prefix', 0),
        // 缓存前缀
        'prefix'=>  \think\facade\Env::get('redis.prefix', ''),
        'password'=>  \think\facade\Env::get('redis.password', ''),
        'select'=>  \think\facade\Env::get('redis.select', 0),
        'timeout'=>  \think\facade\Env::get('redis.timeout', 60),
        'persistent'=>  \think\facade\Env::get('redis.persistent', false),
        'serialize'=>  \think\facade\Env::get('redis.serialize', true),
    ],
    // 添加更多的缓存类型设置
];