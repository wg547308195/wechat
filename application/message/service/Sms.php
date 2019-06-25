<?php
/**
 * Created by SanGuoYun.
 * User: xuewl
 * Date: 2018/2/11
 * Time: 14:55
 */
namespace app\message\service;

use app\common\library\Service;
use Flc\Dysms\Client;
use Flc\Dysms\Request\SendSms;

class Sms extends Service
{

    protected $config;

    public function _initialize()
    {
        parent::_initialize();
        $this->config = config('service.sms');
    }

    /**
     * 短信发送接口
     * @param  [type] $mobile [description]
     * @param  [type] $tpl_id [场景]
     * @param  array  $params [mobile=>123,code =>123]
     * @param  array  $extra  [description]
     * @return [type]         [description]
     */
    public function send($mobile, $tpl_id, $params = [], $extra = []) {
        $tpl_code = config('service.sms.tpls.'.$tpl_id);
        if(!$tpl_code){
            $this->error = '短信场景无效';
            return false;       
        }
      
        //注册场景,修改密码,信息变更,异地登陆,身份验证 时获取验证码
        if('login' == $tpl_id || 'register' == $tpl_id || 'validate' == $tpl_id || 'delivery' == $tpl_id){
            $params['code'] = $this->get_verification_code($tpl_id, $mobile, 4);
        }
        $client  = new Client($this->config);
        $sendSms = new SendSms;
        $sendSms->setPhoneNumbers($mobile);
        $sendSms->setSignName($this->config['sign']);
        $sendSms->setTemplateCode($tpl_code);
        $sendSms->setTemplateParam($params);
        $result = $client->execute($sendSms);
        $send = ($result->Code == 'OK') ? true : false;

        if($send === false)
        {
            $this->error = $result->Message;
            return false;
        }
        return true;
    }

    /**
     * 检测验证码
     * @param        $mobile
     * @param        $code
     * @param string $tpl_id
     */
    public function valid($mobile, $code, $tpl_id = '') {

    }
    /**
     * 生成验证码
     * @param  string $from   [场景，注册：register，修改密码：fax]
     * @param  string $length     []
     */
    public function get_verification_code($from = 'register', $mobile = '', $length = 4)
    {
        $key = md5($from.$mobile);
        $code = cache($key);
        if(!$code) {
            $code = $this->generate_code($length);
            cache($key,$code,300);
            \Log::write("生成短信验证码(".$from.$mobile.")".print_r($code, true), 'debug');
        }
        return $code;
    }
    /**
     * 验证码检测
     * @param  string $sms  [验证码]
     * @param  string $from [场景，注册：register，修改密码：fax]
     */
    public function check_verification_code($from = 'register' ,$code = '0' ,$mobile = '')
    { 
        //万能验证码
        if($code == '123456' && \Env::get('app_status', 'local') != 'online')
            return true; 
        $cachekey = md5($from.$mobile);
        $cachecode = cache($cachekey);
        if(!$cachecode)
        {
            $this->error = '验证码不存在';
            return false;
        }

        if($cachecode != $code) {
            $this->error = '验证码错误';
            return false;
        }
        cache($cachekey,null);
        return true;
    }
    /**
     * 生成随机数
     * @param  string $length [长度]
     * @return [type]         [description]
     */
    public function generate_code($length='4')
    {
        return rand(pow(10,$length-1),(pow(10,$length)-1));
    }
}
