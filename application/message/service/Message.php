<?php
namespace app\message\service;

use app\common\library\Service;

class Message extends Service
{
	
	public function _initialize() {
        parent::_initialize();
    }

    public function set_message(array $message, array $fromUser)
    {
    	try {
   
    		switch ($message['MsgType']) {
		        case 'event':
		        	//订阅或取消订阅
		        	if($message['Event'] == 'subscribe' || $message['Event'] == 'unsubscribe')
		        		$ret = $this->set_user_info($fromUser);
		        		if(!$ret) 
		        			return false;
		            return  "{$fromUser['nickname']} 您好！欢迎关注我的公众号!";
		            break;
		        case 'text':
		            return '收到文字消息';
		            break;
		        case 'image':
		            return '收到图片消息';
		            break;
		        case 'voice':
		            return '收到语音消息';
		            break;
		        case 'video':
		            return '收到视频消息';
		            break;
		        case 'location':
		            return '收到坐标消息';
		            break;
		        case 'link':
		            return '收到链接消息';
		            break;
		        case 'file':
		            return '收到文件消息';
		        // ... 其它消息
		        default:
		            return '收到其它消息';
		            break;
		    }	
    		
    	} catch (\Exception $e) {
    		\Log::write("[消息处理]".print_r($fromUser, true), 'debug');
    		return false;
    	}

    	return true;
    }

    //设置用户信息
    public function set_user_info($info = [])
    {

        try {
            //取消关注
            if($info['subscribe'] == 0){
                $user_info = model('user/so_user','service')->wechat_delete($info['openid']);
                if(!$info){
                    return '服务器发生错误，请稍后再试！'; 
                }
                return $user_info;
            }

            $user_info = model('user/so_user')->where('openid',$info['openid'])->find();
            //没有信息时记录信息
            if(empty($user_info)){
                $data = [
                    'openid' => $info['openid'],
                    'nick_name' => $info['nickname'],
                    'mobile' => '',
                    'sex' => $info['sex'],
                    'city' => $info['city'],
                    'province' => $info['province'],
                    'headimgurl' => $info['headimgurl'],
                ];
                $user_info = model('user/so_user','service')->create($data);
                if(!$user_info){
                    return '服务器发生错误，请稍后再试！'; 
                } 
            }
            
        } catch (\Exception $e) {
            \Log::write("[设置用户信息]".print_r($e->getMessage(), true), 'debug');
            return false;
        }
        return $user_info;
    }

}