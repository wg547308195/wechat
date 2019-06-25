<?php

//支付配置

return [
    // 阿里大鱼短信配置
    'sms' => [
        'accessKeyId'     => 'LTAIGs5qkw4CybaD',
        'accessKeySecret' => '9G67LAYqOVzPK4dEKQxy1FuaTxefzy',
        // 'sign'        => '哈珠科技',
        'sign'        => '土尔齐',
        'tpls'            => [
            'login'      => 'SMS_155190220',//用户登陆：验证码${code}，您正在登录，若非本人操作，请勿泄露。
            'register'   => 'SMS_155190218',//用户注册：验证码${code}，您正在注册成为新用户，感谢您的支持！
            'validate'   => 'SMS_155190221',//用户验证：验证码${code}，您正在进行身份验证，打死不要告诉别人哦！
            'delivery'   => 'SMS_155550305',//发货通知：尊敬的${name}，您的订单${order}已到货，请到店出示${code}领取。
        ]
    ]
];
