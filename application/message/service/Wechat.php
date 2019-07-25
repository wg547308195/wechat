<?php
namespace app\message\service;

use app\common\library\Service;

class Wechat extends Service
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
		        	if($message['Event'] == 'subscribe' || $message['Event'] == 'unsubscribe'){
                        $ret = $this->set_user_info($fromUser);
                        if(!$ret) {
                            return false;
                        }
                    }
                    $ret_message = "{$fromUser['nickname']} 您好！欢迎关注哈珠科技！";
                    $ret_message .= "点击进入<a href='".url('user/my/index','','',true)."'>用户中心</a>！";
                    \Log::write("[地址]".print_r($ret_message, true), 'debug');
		            return  $ret_message;
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
                $user_info = model('user/so_user')->save(['subscribe'=>0,'subscribe_time'=>''],['openid'=>$info['openid']]);
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
                    'nickname' => $info['nickname'],
                    'sex' => $info['sex'],
                    'city' => $info['city'],
                    'province' => $info['province'],
                    'headimgurl' => $info['headimgurl'],
                    'subscribe' => 1,
                    'subscribe_time' => date('Y-m-d H:i:s',time())
                ];
                \Log::write("[1111]".print_r($data, true), 'debug');
                $user_info = model('user/so_user','service')->create($data);
                \Log::write("[2222]".print_r($user_info, true), 'debug');
                if(!$user_info){
                    return '服务器发生错误，请稍后再试！'; 
                } 
            }else{
                model('user/so_user')->save(['subscribe'=>1,'subscribe_time'=>date('Y-m-d H:i:s',time())],['openid'=>$info['openid']]);
            }
            
        } catch (\Exception $e) {
            \Log::write("[设置用户信息]".print_r($e->getMessage(), true), 'debug');
            return false;
        }
        return $user_info;
    }

}