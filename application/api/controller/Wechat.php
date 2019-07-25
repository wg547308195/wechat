<?php
namespace app\api\controller;

use EasyWeChat\Factory;
use think\facade\Session;
use EasyWeChat\Kernel\Messages\Text;
 use think\Controller;
class Wechat
{

    private $options;

    public function __construct() {
        $config = config('wechat.wechat');
        $this->options = [
            'app_id' => $config['wechat_app_id'],
            'secret' => $config['wechat_secret'],
            'token' => $config['wechat_token']
        ];
        //定义token时使用，其他时候注释掉
        //$this->valid();
    }

    public function index(){
        try {
            $app = Factory::officialAccount($this->options);
            $user = $app->user;

            $app->server->push(function($message) use ($user) {
                $fromUser = $user->get($message['FromUserName']);
                \Log::write("[用户信息]".print_r($fromUser, true), 'debug');
                \Log::write("[消息]".print_r($message, true), 'debug');

                //处理消息
                $ret_message = model('message/wechat','service')->set_message($message,$fromUser);
                if(!$ret_message){
                    return '服务器发生错误，请稍后再试！'; 
                }
                return $ret_message;
            });

            $app->server->serve()->send();
            
        } catch (\Exception $e) {
            \Log::write("[错误消息]".print_r($e->getMessage(), true), 'debug');
        }
    }

    //数据存储
    public  function profile()
    {   
        $url = 'http://'.$_SERVER['HTTP_HOST'];
        $target_url = substr($_SERVER['REQUEST_URI'], 1,strlen($_SERVER['REQUEST_URI']));

        $_options = [
            'app_id' => $this->options['app_id'],
            'secret' => $this->options['secret'],
            'token' => $this->options['token'],
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => $url.'/api/wechat/oauth_callback?target='.$target_url,
            ],
        ];
        $app = Factory::officialAccount($_options);
        $oauth = $app->oauth;
        Session::set('target_url', $target_url);
        \Log::write("[记录内容]".print_r(Session::get('target_url'), true), 'debug');
        $oauth->redirect()->send();
        \Log::write("[记录请求]".print_r($oauth->redirect()->send(), true), 'debug');
    }


    //回调
    public  function oauth_callback()
    {

        $app = Factory::officialAccount($this->options);
        $oauth = $app->oauth;

        // 获取 OAuth 授权结果用户信息
        $user = $oauth->user();
        Session::set('wechat_user' ,$user->toArray());
        $targetUrl = empty(Session::get('target_url')) ? '/' : Session::get('target_url');
        \Log::write("[用户信息]".print_r(Session::get('wechat_user') , true), 'debug');
        return redirect($targetUrl);
    }


    public function valid()
    {
        $echoStr = $_GET["echostr"];
        \Log::write("[111]".print_r($echoStr , true), 'debug');
        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = file_get_contents("php://input");
        //extract post data
        if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";             
                if(!empty( $keyword ))
                {
                    $msgType = "text";
                    $contentStr = "Welcome to wechat1 world!";

                    if($keyword == '123'){
                        $contentStr = 'http://mcmhhs.natappfree.cc/api/v1/custom/wechat_detail?openid=2';
                    }

                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    \Log::write("[收到信息]".print_r($keyword, true), 'debug');
                    echo $resultStr;
                }else{
                    \Log::write("[错误输出]".print_r('Input something...', true), 'debug');
                    echo "Input something...";
                }

        }else {
            echo "";
            exit;
        }
    }


    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = $this->options['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

}
