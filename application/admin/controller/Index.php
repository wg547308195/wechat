<?php
namespace app\admin\controller;

use think\Request;
use think\response\Json;

class Index extends Admin
{
    /**
     * 首页
     */
    public function index()
    {
        $site_info = model('setting/SysSetting','service')->info();
        $site_info['site_name'] = !empty($site_info['site_name']) ? $site_info['site_name'] : '后台管理系统';
        $site_info['site_title'] = !empty($site_info['site_title']) ? $site_info['site_title'] : '首页-后台管理系统';
        $this->assign('site_info',$site_info);
        return $this->fetch('index');
    }

    /**
     * 控制台
     */
    public function home()
    {
        //当日新增用户数
        $today_user = model('user/so_user')->whereTime('create_time', 'today')->count();
        //当月新增用户数
        $month_user = model('user/so_user')->whereTime('create_time', 'month')->count();
        //累计用户数
        $total_user = model('user/so_user')->count();
        //累计产品数
        $count_goods = model('goods/so_goods')->count();
        
        $this->assign('today_user',$today_user)
             ->assign('month_user',$month_user)
             ->assign('total_user',$total_user)
             ->assign('count_goods',$count_goods);
        return $this->fetch('home');
    }

    /**
     * 数据概览
     */
    public function ajax_home(){
        $day_num = 15;
        $dates = [];
        for ($i = 1; $i <= $day_num; $i++) {
            $dates[] = date('m-d', strtotime('-'.$i.' days'));
        }
        sort($dates);

        $res = [
            'dates' => $dates,
            'user' => [],
            'custom' => [],
        ];

        // 新增用户走势图
        $sql = "SELECT FROM_UNIXTIME(create_time, '%m-%d') AS time, count(*) AS num";
        $sql .= " FROM so_user";
        $sql .= " WHERE DATE_SUB(CURDATE(), INTERVAL ".count($dates)." DAY) <= DATE(FROM_UNIXTIME(create_time, '%Y-%m-%d'))";
        $sql .= " GROUP BY time";
        $add_users = \think\Db::query($sql);
        $add_users_arr = [];
        if ($add_users) {
            foreach ($add_users as $v) {
                $add_users_arr[$v['time']] = $v['num'];
            }
        }

        //新增经销商走势图
        $sql = "SELECT FROM_UNIXTIME(create_time, '%m-%d') AS time, count(*) AS num";
        $sql .= " FROM so_custom";
        $sql .= " WHERE DATE_SUB(CURDATE(), INTERVAL ".count($dates)." DAY) <= DATE(FROM_UNIXTIME(create_time, '%Y-%m-%d'))";
        $sql .= " GROUP BY time";
        $add_customs = \think\Db::query($sql);
        $add_customs_arr = [];
        if ($add_customs) {
            foreach ($add_customs as $v) {
                $add_customs_arr[$v['time']] = $v['num'];
            }
        }

        $users = $customs = [];
        foreach ($dates as $k => $v) {
            //组装用户数据
            $users[$k] = 0;
            if (isset($add_users_arr[$v])) {
                $users[$k] = $add_users_arr[$v];
            }
            //组装经销商数据
            $customs[$k] = 0;
            if (isset($add_customs_arr[$v])) {
                $customs[$k] = $add_customs_arr[$v];
            }
        }
        $res['user'] = $users;
        $res['custom'] = $customs;
        

        return Json::create(['code' => 1,'msg' => '请求成功', 'info' => $res]);
    }

    /**
     * 经销商用户排名
     */
    public function ajax_top_custom(){
        $field = 'id,username,nickname,(select count(*) from so_user as u where u.custom_id=so_custom.id) as count';
        $order = 'count DESC';
        $result = model('custom/SoCustom','service')->lists([],$order,false,false,$field);

        return Json::create(['code' => 0,'msg' => '请求成功', 'data' => $result, 'count' => count($result)]);
    }
}