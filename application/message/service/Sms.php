<?php
/**
 * Created by SanGuoYun.
 * User: 李勇
 * Date: 2019/6/27
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
        $_config = model('setting/SysSetting','service')->info();
        $this->config = [
            'accessKeyId' => $_config['sms_accessKeyId'],
            'accessKeySecret' => $_config['sms_accessKeySecret'],
            'sign' => $_config['sms_sign']
        ];
    }

    /**
     * 短信发送接口（单条）
     * @param  [type] $mobile [发送对象：支持多个用数组传入，上限为20个]
     * @param  [type] $tpl_id [模板id]
     * @param  array  $params [mobile=>123,code =>123]
     * @param  array  $extra  [description]
     * @return [type]         [description]
     */
    public function send_one($mobile, $tpl_id, $params = [], $is_valid = 0) {
        if (empty($mobile)){
            $this->error = '短信发送对象不能为空';
            return false;
        }
        $tpl = model('message/sms_template')->where('template_id','=',$tpl_id)->find();
        if (empty($tpl->id)){
            $this->error = '短信模板不存在';
            return false;
        }
        if ($tpl->status == -1){
            $this->error = '该模板未启用';
            return false;
        }
        //带验证码
        if($is_valid == 1){
            $params['code'] = $this->get_verification_code($tpl_id, $mobile, 4);
        }
        $client  = new Client($this->config);
        $sendSms = new SendSms;
        $sendSms->setPhoneNumbers($mobile);
        $sendSms->setSignName($this->config['sign']);
        $sendSms->setTemplateCode($tpl_id);
        $sendSms->setTemplateParam($params);
        $result = $client->execute($sendSms);
        $send = ($result->Code == 'OK') ? true : false;

        if($send === false){
            $this->error = $result->Message;
            return false;
        }
        return true;
    }

    /**
     * 短信发送接口（多条，不支持验证码）
     * @param  [type] $mobile [发送对象：支持多个用数组传入，上限为20个]
     * @param  [type] $tpl_id [模板id]
     * @param  array  $params [mobile=>123,code =>123]
     * @param  array  $extra  [description]
     * @return [type]         [description]
     */
    public function send_all($mobile = [], $tpl_id, $params = [], $extra = []) {
        if (empty($mobile)){
            $this->error = '短信发送对象不能为空';
            return false;
        }
        $tpl = model('message/sms_template')->where('template_id','=',$tpl_id)->find();
        if (empty($tpl->id)){
            $this->error = '短信模板不存在';
            return false;
        }
        if ($tpl->status == -1){
            $this->error = '该模板未启用';
            return false;
        }
        $mobile = array_chunk($mobile, 20);
        foreach ($mobile as $key => $value) {
            $client  = new Client($this->config);
            $sendSms = new SendSms;
            $sendSms->setPhoneNumbers($value);
            $sendSms->setSignName($this->config['sign']);
            $sendSms->setTemplateCode($tpl_id);
            $sendSms->setTemplateParam($params);
            $result = $client->execute($sendSms);
            $send = ($result->Code ==  'OK') ? true : false;

            if($send === false){
                $this->error = $result->Message;
                return false;
            }
        }
        
        return true;
    }

    /**
     * 生成验证码
     * @param  string $tpl_id   [模板id]
     * @param  string $mobile   [手机号码]
     * @param  string $length   [验证码长度]
     */
    public function get_verification_code($tpl_id = '', $mobile = '', $length = 4)
    {
        $key = md5($tpl_id.$mobile);
        $code = cache($key);
        if(!$code) {
            $code = $this->generate_code($length);
            cache($key,$code,300);
            \Log::write("生成短信验证码(".$tpl_id.$mobile.")".print_r($code, true), 'debug');
        }
        return $code;
    }

    /**
     * 验证码检测
     * @param  string $tpl_id  [模板id]
     * @param  string $code    [验证码]
     * @param  string $mobile  [手机号码]
     */
    public function check_verification_code($tpl_id = '' ,$code = '0' ,$mobile = '')
    { 
        $cachekey = md5($tpl_id.$mobile);
        $cachecode = cache($cachekey);
        if(!$cachecode) {
            $this->error = '验证码已过期，请重新发送！';
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
