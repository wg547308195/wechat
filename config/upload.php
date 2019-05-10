<?php

//上传配置

return [
    /**
     * 站点访问地址
     */
    //'siteurl' => 'http://192.168.0.88/sgys/public',
    'siteurl' => \think\facade\Env::get('upload.upload_url', ''),                    
    /**
     * 上传地址,默认是本地上传,如果需要使用又拍云则改为http://v0.api.upyun.com/yourbucketname
     */
    'uploadurl' => 'attachment/attachment/upload',
    /**
     * 本机的CDN地址或又拍云http://yourbucketname.b0.upaiyun.com
     */
    'cdnurl'    => '',
    /**
     * 上传成功后的通知地址
     */
    'notifyurl' => 'http://www.yoursite.com/upyun/notify',
    /**
     * 又拍云Bucket
     */
    'bucket'    => 'yourbucketname',
    /**
     * 生成的policy有效时间
     */
    'expire'    => '86400',
    /**
     * 又拍云formkey
     */
    'formkey'   => '',
    /**
     * 文件保存格式
     */
    'savekey'   => '/uploads/{year}{mon}{day}/{folder}/{filemd5}{.suffix}',
    /**
     * 最大可上传大小
     */
    'maxsize'   => '10mb',
    /**
     * 可上传的文件类型
     */
    'mimetype'  => '*',
    /**
     * 是否支持批量上传
     */
    'multiple'  => false,
    /**
     * 又拍云操作员用户名
     */
    'username'  => '',
    /**
     * 又拍云操作员密码
     */
    'password'  => '',
    //app保存路径
    'upload_dir_app'   => '/apps/',
    //上传apk文件大小
    'maxsize_app'   => '50mb',
];
