<?php

namespace fast;
/**
 * php 数组助手类
 * Class ArrayHelper
 * @package app\helper
 */
class ArrayHelper {
    /**
     * @brief   get_ids_arr     取得某个二维数组里的id集合
     *-----------------------------------------------
     *  $arr = array(
     *              array('line_id'  =>  1, 'title'    =>  '标题1',),
     *              array('line_id'  =>  2, 'title'    =>  '标题2',),
     *          );
     *  $line_ids = $this->share->get_ids_arr($arr);
     *-----得到--------------------------------------
     *  $line_ids = array(1, 2);
     *-----------------------------------------------
     * @Param   $arr            原始数组
     * @Param   $field          需要的字段：如 id, line_id, cid...
     * @Param   $zero           是否增加一个元素0，防止空数组导致where_in('id', $ids)出错
     *
     * @Returns Array
     */
    public static function get_ids_arr($arr = array(array('id'=>1, 'other'=>''),), $field = 'id', $zero = false){
        $new_arr = array();
        foreach ($arr as $ak=>$av) {
            if (!array_key_exists($field, $av)) {
                break;      //非法数组
            }
            $new_arr[] = $av[$field];
        }
        if (empty($new_arr) && $zero) {
            $new_arr[] = 0;
        }

        return $new_arr ? array_unique($new_arr) : $new_arr;
    }


    /**
     * @brief   reform_arr  重组数组
     * --------------------------------------------
     *  $arr = array(
     *              array('line_id'  =>  11, 'title'    =>  '标题1',),
     *              array('line_id'  =>  22, 'title'    =>  '标题2',),
     *          );
     *  $new_arr = $this->share->reform_arr($arr);
     * ----得到------------------------------------
     *  array(
     *          11=>array('line_id'  =>  11, 'title'    =>  '标题1',),
     *          22=>array('line_id'  =>  22, 'title'    =>  '标题2',),
     *      );
     * --------------------------------------------
     * @Param   $arr
     * @Param   $field
     *
     * @Returns Array
     */
    public static function reform_arr($arr = array(array('id'=>1, 'other'=>''),), $field = 'id'){
        $new_arr = array();
        if (!is_array($arr)) {
            return $new_arr;
        }
        foreach ($arr as $av) {
            if (!is_array($av)) {
                break;
            }
            if (!array_key_exists($field, $av)) {
                break;
            }
            if (!isset($new_arr[$av[$field]])) {
                $new_arr[$av[$field]] = $av;
            }
        }
        return $new_arr;
    }

    /**
     * Builds a map (key-value pairs) from a multidimensional array or an array of objects.
     * The `$from` and `$to` parameters specify the key names or property names to set up the map.
     * Optionally, one can further group the map according to a grouping field `$group`.
     *
     * For example,
     *
     * ~~~
     * $array = [
     *     ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
     *     ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
     *     ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
     * ];
     *
     * $result = ArrayHelper::map($array, 'id', 'name');
     * // the result is:
     * // [
     * //     '123' => 'aaa',
     * //     '124' => 'bbb',
     * //     '345' => 'ccc',
     * // ]
     *
     * $result = ArrayHelper::map($array, 'id', 'name', 'class');
     * // the result is:
     * // [
     * //     'x' => [
     * //         '123' => 'aaa',
     * //         '124' => 'bbb',
     * //     ],
     * //     'y' => [
     * //         '345' => 'ccc',
     * //     ],
     * // ]
     *
     * $result = ArrayHelper::map($array, 'id', 'name', 'class',true);
     * // the result is:
     * // [
     * //     'x' => [
     * //         '123' => ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
     * //         '124' => ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
     * //     ],
     * //     'y' => [
     * //         '345' => ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
     * //     ],
     * // ]
     * ~~~
     *
     * @param array $array
     * @param string|\Closure $from
     * @param string|\Closure $to
     * @param string|\Closure $group
     * @param boolen|\Closure $back_arr
     * @return array
     */
    public static function map($array, $from, $to, $group = null, $back_arr = false)
    {
        if(!is_array($array)){
            return array();
        }
        $result = [];
        foreach ($array as $element) {
            if($from != null && !array_key_exists($from,$element) OR $to != null && !array_key_exists($to,$element))
            {
                continue;
            }
            $key   = $element[$from];
            $value = $element[$to];
            if ($group !== null) {
                if(!array_key_exists($group,$element))
                {
                    continue;
                }
                if ($back_arr == true) {
                    foreach ($array as $k => $v) {
                        $key_val = $from ? $v[$from] : $k;
                        $result[$v[$group]][$key_val] = $v;
                    }
                } else {
                    $result[$element[$group]][$key] = $value;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }


    /**
     * @brief   get_ids_str     取得某个二维数组里的id集合,组成字符串
     *-----------------------------------------------
     *  Array
    (
    [0] => Array
    (
    [id] => 9613
    [total] => 4
    )
    [1] => Array
    (
    [id] => 1234
    [total] => 5
    )

    )
     *  $line_ids = $this->share->get_ids_str($arr);
     *-----得到--------------------------------------
     *  $line_ids = 9613,1234;
     *-----------------------------------------------
     * @Param   $arr            原始数组
     * @Param   $field          需要的字段：如 id, line_id, cid...
     * @Param   $zero           是否增加一个元素0，防止空数组导致WHERE id IN('')出错
     *
     * @Returns string
     */
    public static function get_ids_str($arr = array(array('id'=>1, 'other'=>''),), $field = 'id', $zero = false){
        return join(',', self::get_ids_arr($arr, $field));
    }

    /**
     * @brief   array_pop_ele_byval     根据指定值剔除数组中的元素
     * ---------------------------------------------
     *  Example
     *      $arr = array('a'=>'hello', 'b'=>'abc', 'c'=>'hello');
     *      $arr = $this->share->array_pop_ele_byval($arr, 'hello');
     *      print_r($arr);exit;
     * --------------------------------------------
     * @Param   $arr
     * @Param   $val
     *
     * @Returns Array
     */
    public static function array_pop_ele_byval($arr, $val = ''){
        if (!is_array($arr)) {
            return false;
        }
        foreach ($arr as $ak=>$av) {
            if ($av == $val) {
                unset($arr[$ak]);
            }
        }
        return $arr;
    }


    /**
     * @brief   array_pop_ele_bykey     根据指定指定下标剔除元素
     * ---------------------------------------------
     *  Example
     *      $arr = array('a'=>'hello', 'b'=>'abc', 'c'=>'hello');
     *      $arr = $this->share->array_pop_ele_bykey($arr, 'a');
     *      print_r($arr);exit;
     * --------------------------------------------
     * @Param   $arr
     * @Param   $key
     *
     * @Returns Array
     */
    public static function array_pop_ele_bykey($arr, $key=''){
        if (!is_array($arr)) {
            return false;
        }
        foreach ($arr as $ak=>$av) {
            if ($ak == $key) {
                unset($arr[$ak]);
            }
        }
        return $arr;
    }

    /**
    * 多维数组合并（支持多数组）
    * @return array
    */
    public static function array_merge_multi(){
        $args = func_get_args();
        $array = array();
        foreach ( $args as $arg ) {
            if ( is_array($arg) ) {
                foreach ( $arg as $k => $v ) {
                    if ( is_array($v) ) {
                        $array[$k] = isset($array[$k]) ? $array[$k] : array();
                        $array[$k] = self::array_merge_multi($array[$k], $v);
                    } else {
                        $array[$k] = $v;
                    }
                }
            }
        }
        return $array;
    }


    /**
     * @brief   array2sort  二维数组 根据指定下标 排序(冒泡)    保持索引关系
     * --------------------------------------------------------------------
     * $arr = array(
     *     'a'=>array( 'key1'=>3,   'key2'=>50,),
     *     'b'=>array( 'key1'=>79,  'key2'=>30,),
     *     'c'=>array( 'key1'=>8,   'key2'=>40,),
     *     'd'=>array( 'key1'=>55,  'key2'=>20,),
     *     11=>array( 'key1'=>2,   'key2'=>300,),
     *     );
     *  $arr = array2sort($arr, 'key2', 'a');print_r($arr);
     *--------------------------------------------------------------------
     * @Param   $arr        待排序数组,(既可以是索引数组，也可以是关系型数组)
     * @Param   $key        要排序的下标
     * @Param   $sort       d-降序 a-升序
     *
     * @Returns Array
     */
    public function array2sort($arr, $key='', $sort = 'd'){
        $n = count($arr);
        $tmp = array();
        if (empty($arr) || empty($key) || !in_array($sort, array('d', 'a'))) {
            return $arr;
        }
        foreach ($arr as $ak=>$av) {            //为保持索引关系，将Key压入数组最后一个元素值保存
            array_push($arr[$ak], $ak);
        }
        $arr = array_values($arr);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $n-1; $j > $i; $j--) {
                //降序排列
                if ($sort == 'd') {
                    if (isset($arr[$i][$key]) && $arr[$i][$key] < $arr[$j][$key]) {
                        $tmp = $arr[$i];
                        $arr[$i] = $arr[$j];
                        $arr[$j] = $tmp;
                    }

                    //升序排列
                } else {
                    if (isset($arr[$i][$key]) && $arr[$i][$key] > $arr[$j][$key]) {
                        $tmp = $arr[$j];
                        $arr[$j] = $arr[$i];
                        $arr[$i] = $tmp;
                    }
                }
            }
        }

        $new_arr = array();
        foreach ($arr as $ak=>$av) {        //为保持索引关系，将最右一个元素值key，拿出来放到下标里
            $tmp_key = array_pop($arr[$ak]);
            $new_arr[$tmp_key] = $arr[$ak];
        }
        return $new_arr;
    }

    /**
     * 把返回的数据集转换成Tree
     * @access public
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $level level标记字段
     * @return array
     */
    public static function list_to_tree($list, $pk='id',$pid = 'pid',$child = '_children',$root=0) {
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }


    /**
     * 递归获取所有上级
     * @param [string] $str      [当前层级代码]
     * @param [string] $length   [顶级层级代码长度]
     * return array
     */
    public static function cut_str($str = '',$length = 0,&$result = []){
        if (strlen($str)-3 > $length){
            $_str = \fast\Util::msubstr($str, 0, strlen($str)-3, 'utf-8', false);
            $result[] = $_str;
            self::cut_str($_str, $length, $result);
        }
        if (!in_array($str, $result)){
            $result[] = $str;
        }
        return $result;
    }


    /**
     * cz 用于统计
     */
    public static function statistics($starttime,$endtime,$date_type,$page,$per_page){
        $endtime = strtotime(date('Y-m-d',$endtime)) + 86400 -1;
        if($endtime > time()){
            $endtime = time();
        }

        switch ($date_type) {
            case 'days':
                $info['groupby'] = "DATE_FORMAT(from_unixtime(create_time),'%Y%m%d')";
                $info['total'] = ceil((($endtime-$starttime)+1)/86400);
                ////正序
//                $info['endtime'] = $starttime+($per_page*86400*$page);
//                if($page >= 2){
//                    $info['starttime'] = $starttime+($per_page*86400*($page-1));
//                }else{
//                    $info['starttime'] = $starttime;
//                }

                //倒叙
                if($page >= 2){
                    $info['endtime'] = $endtime-($per_page*86400*($page-1));
                }else{
                    $info['endtime'] = $endtime;
                }
                $info['starttime'] = $endtime-($per_page*86400*$page);

                if($info['total'] > $per_page){
                    $total = $per_page;
                }else{
                    $total = $info['total'];
                }
                $info['arrs'] = [];
                for($i=0;$i<$total;$i++){
                    $days = $info['endtime']-(86400*$i);
                    $info['arrs'][] = mb_substr(date("Ymd",$days),0,8);
                    if((int)$days <= (int)$starttime){
                        break; 
                    }
                }

                break;
            case 'weeks':
                $info['groupby'] = "DATE_FORMAT(from_unixtime(create_time),'%Y%u')";
                $week = self::numweeka($starttime,$endtime);

                $info['total'] = count($week);
                $weeks = array_chunk($week,$per_page);
                //正序
//                if($info['total'] <= $per_page){
//                    $info['endtime'] = strtotime(end($week)[1]);
//                }else{
//                    $info['endtime'] = strtotime(end($weeks[$page-1])[1]);
//                }
//
//                if($info['endtime'] > $endtime){
//                    $info['endtime'] = $endtime;
//                }
//                if($page >= 2){
//                    $info['starttime'] = strtotime(end($weeks[$page-2])[1]);
//                }else{
//                    $info['starttime'] = $starttime;
//                }

                //倒叙
                if($page >= 2){
                    $info['endtime'] = strtotime($weeks[$page-1][0][1]);
                    $info['starttime'] = strtotime(end($weeks[$page-1])[0]);
                }else{
                    $info['endtime'] = strtotime($week[0][1]);
                    $info['starttime'] = strtotime(end($weeks[0])[0]);
                }

                if($info['total'] > $per_page){
                    $total = $per_page;
                }else{
                    $total = $info['total'];
                }

                $info['arrs'] = [];
                for($i=0;$i<$total;$i++){
                    $info['arrs'][] = date('oW',$info['endtime']-(86400*7*$i));
                    if($endtime <= $info['starttime']+(86400*7*$i))
                        break;
                }
                break;
            case 'months':
                $info['groupby'] = "DATE_FORMAT(from_unixtime(create_time),'%Y%m')";
                $month = self::getMonthArr($starttime,$endtime);

                $info['total'] = count($month);
                $months = array_chunk($month,$per_page);

                //正序
//                if($info['total'] <= $per_page){
//                    $enddate = end($month);
//                    $info['endtime'] = strtotime("$enddate +1 month -1 day");
//                }else{
//                    $enddate = end($months[$page-1]);
//                    $info['endtime'] = strtotime("$enddate +1 month -1 day");
//                }
//
//                if($info['endtime'] > $endtime){
//                    $info['endtime'] = $endtime;
//                }
//
//                if($page >= 2){
//                    $info['starttime'] = strtotime(reset($months[$page-1]));
//                }else{
//                    $info['starttime'] = $starttime;
//                }


                //反序
                if($page >= 2){
                    $enddate = $months[$page-1][0];
                    $info['endtime'] = strtotime("$enddate +1 month -1 day");
                    $info['starttime'] = strtotime(end($months[$page-1]));
                }else{
                    $enddate = $months[0][0];
                    $info['endtime'] = strtotime("$enddate +1 month -1 day");
                    $info['starttime'] = strtotime(end($months[0]));
                }

                if($info['endtime'] > time()){
                    $info['endtime'] = time();
                }

                if($info['total'] > $per_page){
                    $total = $per_page;
                }else{
                    $total = $info['total'];
                }

                $info['arrs'] = [];
                $y = 0;
                $m = 0;
                for($i=0;$i<$total;$i++){
                    $m = date("m",$info['endtime']);
                    $y = date("Y",$info['endtime']);

                    $m = $m - $i;

                    if($m <= 0){
                        $y--;
                        $m = $m + 12;
                    }
                    $m = substr('0'.$m, strlen($m)-1,2);

                    $info['arrs'][] = "$y$m";    
                    if((int)"$y$m" <= (int)date('Ym',$info['starttime']))
                        break;
                }
                break;
            case 'quarter':
                $info['groupby'] = "concat(DATE_FORMAT(from_unixtime(create_time), '%Y'),FLOOR((DATE_FORMAT(from_unixtime(create_time), '%m')+2)/3))";
                $quarter = self::getQuarterArr($starttime,$endtime);

                //反序
                $quarter = array_reverse($quarter);
                $info['total'] = count($quarter);
                $quarters = array_chunk($quarter,$per_page);
            
                //每页的开始结束时间
                $info['endtime'] = strtotime(explode(',',reset($quarters[$page-1]))[1] .' 23:59:59');
                $info['starttime'] = strtotime(explode(',',end($quarters[$page-1]))[0]);

                if($info['starttime'] <= date('Y-m-d',$starttime)){
                    $info['starttime'] = date('Y-m-d',$starttime);             
                }
    
                if($info['total'] > $per_page){
                    $total = $per_page;
                }else{
                    $total = $info['total'];
                } 
                $info['arrs'] = [];
                for($i=0;$i<$total;$i++){
                    //数组拆分，记录开始时间，并将开始时间再次拆分
                    $quarters_time = explode(',',($quarters[$page-1][$i])); 
                    $end_time = $quarters_time[0];
                    $quarters_time = explode('-', $quarters_time[0]);
                    
                    if((int)$quarters_time[1] <= 3){
                        $info['arrs'][] = $quarters_time[0].'1'; 
                    }elseif((int)$quarters_time[1] <= 6 && (int)$quarters_time[1] >= 4){
                        $info['arrs'][] = $quarters_time[0].'2';   
                    }elseif((int)$quarters_time[1] <= 9 && (int)$quarters_time[1] >= 7){
                        $info['arrs'][] = $quarters_time[0].'3';   
                    }else{
                        $info['arrs'][] = $quarters_time[0].'4';   
                    }

                    if($end_time <= date('Y-m-d',$starttime))
                        break;
                }
                break;
            case 'years':
                $info['groupby'] = "DATE_FORMAT(from_unixtime(create_time),'%Y')";
                $info['total'] = date('Y',$endtime)-date('Y',$starttime) + 1;
                // $info['endtime'] =strtotime((date('Y',$starttime) + ($per_page*$page)-1).'-12-31') ;
                // $info['endtime'] = $info['endtime'] > $endtime ? $endtime:$info['endtime'];
                // $info['endtime'] = strtotime(date('Y',$info['endtime']).'-12-31 23:59:59') ;

                // if($page >= 2){
                //     $info['starttime'] = strtotime((date('Y',$starttime) + ($per_page * ($page-1))).'-01-01 00:00:00');
                // }else{
                //     $info['starttime'] = $starttime;
                // }
                // if($info['total'] > $per_page){
                //     $total = $per_page;
                // }else{
                //     $total = $info['total'];
                // }
                // $info['arrs'] = [];
                // for($i=0;$i<$total;$i++){
                //     $y = date('Y',$info['starttime']) + $i;
                //     $info['arrs'][] = (string)$y;
                //     if((string)date('Y',$info['endtime']) == $y)
                //         break;    
                // }
                
                //反序
                $info['endtime'] = date('Y',$endtime) - ($per_page * ($page-1));    
                $info['starttime'] = date('Y',$endtime) - ($per_page * $page) + 1;
                if( (int)$info['starttime'] <= date('Y' ,$starttime)){
                    $info['starttime'] = (int)date('Y' ,$starttime);
                }
                $info['arrs'] = [];
                if($info['total'] > $per_page){
                    $total = $per_page;
                }else{
                    $total = $info['total'];
                }
                for($i=0;$i<$total;$i++){
                    $y = $info['endtime'] - $i;
                    $info['arrs'][] = (string)$y;
                    if((string)$info['starttime'] == $y)
                        break;    
                }
                $info['endtime'] = strtotime($info['endtime'].'-12-31 23:59:59');
                $info['starttime'] = strtotime($info['starttime'].'-01-01 00:00:00');
                break;
            default:
                $info['groupby'] = '';
                break;
        }

        return $info;
    }



    /**
     * @param $starttime
     * @param $endtime
     * @return array 一段时间的周
     */
    public static function numweeka($starttime,$endtime){
        $endDate = date("Y-m-d h:i:s",$endtime);
        $startDate = date("Y-m-d h:i:s",$starttime);
        //跨越天数
        $n = (strtotime($endDate)-strtotime($startDate))/86400;
        //结束时间加一天(sql语句里用的是小于和大于，如果有等于的话这句可以不要)
        $endDate = date("Y-m-d 00:00:00",strtotime("$endDate +1 day"));
        //判断，跨度小于7天，可能是同一周，也可能是两周
        if($n<7){
            //查开始时间 在 那周 的 位置
            $day            = date("w",strtotime($startDate))-1;
            //查开始时间  那周 的 周一
            $week_start        = date("Y-m-d 00:00:00",strtotime("$startDate -{$day} day"));
            //查开始时间  那周 的 周末
            $day            = 7-$day;
            $week_end        = date("Y-m-d 00:00:00",strtotime("$startDate +{$day} day"));
            //判断周末时间是否大于时间段的结束时间，如果大于，那就是时间段在同一周，否则时间段跨两周
            if($week_end>=$endDate){
                $weekList[] =array($startDate,$endDate);
            }else{
                $weekList[] =array($startDate,$week_end);
                $weekList[] =array($week_end,$endDate);
            }
        }else{
            //如果跨度大于等于7天，可能是刚好1周或跨2周或跨N周，先找出开始时间 在 那周 的 位置和那周的周末时间
            $day         = date("w",strtotime($startDate))-1;
            $week_start  = date("Y-m-d 00:00:00",strtotime("$startDate -{$day} day"));
            $day         = 7-$day;
            $week_end    = date("Y-m-d 00:00:00",strtotime("$startDate +{$day} day"));
            //先把开始时间那周写入数组
            $weekList[]  =array($week_start,$week_end);
            //判断周末是否大于等于结束时间，不管大于(2周)还是等于(1周)，结束时间都是时间段的结束时间。
            if($week_end >= $endDate){
                $weekList[] = array($week_end,$endDate);
            }else{
                //N周的情况用while循环一下，然后写入数组
                while($week_end <= $endDate){
                    $start         = $week_end;
                    $week_end    = date("Y-m-d 00:00:00",strtotime("$week_end +7 day"));
                    if($week_end <= $endDate){
                        $weekList[]  = array($start,$week_end);
                    }else{
                        $weekList[]  = array($start,$endDate);
                    }
                }
            }
        }
        rsort($weekList);
        return $weekList;
    }

    /**
     * @param $starttime
     * @param $endtime
     * @return array 一段时间的月
     */
    public static function getMonthArr($start, $end)
    {
        $start = date("Y-m",$start);
        $end = date("Y-m",$end);

        //转为时间戳
        $st = strtotime($start.'-01');
        $et = strtotime($end.'-01');

        $t = $st;
        $i = 0;
        while($t <= $et)
        {
            //这里累加每个月的的总秒数 计算公式：上一月1号的时间戳秒数减去当前月的时间戳秒数
            //看不懂自己想去
            $d[$i] = trim(date('Y-m',$t),' ');
            $t += strtotime('+1 month', $t)-$t;
            $i++;
        }
        rsort($d);
        return $d;
    }
    /**
     * @param $starttime
     * @param $endtime
     * @return array 一段时间的季度
     */
    public static function getQuarterArr($start, $end)
    {
        $st = date('Y-m-d',$start);
        $et = date('Y-m-d',$end);
        $tStr=explode('-',$st);
        $month=$tStr['1'];
        if($month<=3){
            $t2=date("$tStr[0]-03-31");
        }else if($month<=6){
            $t2=date("$tStr[0]-06-30");
        }else if($month<=9){
            $t2=date("$tStr[0]-09-30");
        }else{
            $t2=date("$tStr[0]-12-31");
        }
        $t1=$st;
        $t2=$t2>$et?$et:$t2;
        $timeArr=array();
        while($t2<$et || $t1<=$et){//月为粒度的时间数组
            $timeArr[]=$t1.','.$t2;
            $t1=date('Y-m-d',strtotime("$t2 +1 day"));
            $t2=date('Y-m-d',strtotime("$t1 +3 months -1 day"));
            $t2=$t2>$et?$et:$t2;
        }
        return $timeArr;
    }


}