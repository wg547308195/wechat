<?php
namespace app\attachment\service;

use app\common\library\Service;
use fast\Random;
use think\facade\Config;

class Attachment extends Service
{
    use \app\common\library\traits\Model;

    protected $modelValidate = 'attachment/attachment';

	protected function _initialize() {
        parent::_initialize();
        $this->model = model($this->modelValidate);
    }

    /**
     * 附件上传接口
     * @param [array]  $file   [上传文件详情]
     * @param [string] $folder [上传子目录（模块）]
     * @return array
     */
    public function upload($file,$folder = 'common',$data = []) {
        if (empty($file)) {
            $this->error = "请上传文件";
            return false;
        }
        //判断是否已经存在附件
        $sha1 = $file->hash();
        $uploaded = $this->model->where('sha1', $sha1)->find();
        if ($uploaded) return $uploaded;

        $upload = Config::pull('upload');
        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int) $upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'file';
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{hour}'     => date("H"),
            '{min}'      => date("i"),
            '{sec}'      => date("s"),
            '{folder}'   => $folder,
            '{random}'   => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
            '{filemd5}'  => $sha1,
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);
        $uploadDir = str_replace('//','/',$uploadDir);

        //验证文件
//        $splInfo = $file->validate(['size' => $size]);
        $splInfo = $file->validate(['size' => $size])->move(\Env::get('root_path') . '/public' . $uploadDir, $fileName);
        if (!$splInfo){
            $this->error = $file->getError();
            return false;
        }

        $imagewidth = $imageheight = 0;
        if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf','doc','docx'])) {
            $imgInfo = getimagesize($splInfo->getPathname());
            $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
            $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
        }

        $url = $upload['siteurl'];
        $params = array(
            'filesize'    => $fileInfo['size'],
            'imagewidth'  => $imagewidth,
            'imageheight' => $imageheight,
            'name'   => substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')),
            'imagetype'   => $suffix,
            'imageframes' => 0,
            'mimetype'    => $fileInfo['type'],
            'url'         => $url . $uploadDir . $splInfo->getSaveName(),
            'uploadtime'  => time(),
            'storage'     => 'local',
            'sha1'        => $sha1,
        );

        if(!empty($data)){
            $params = array_merge($params,$data);
        }

        $result = $this->model->create(array_filter($params));
        if ($result === false){
            $this->error = $this->model->getError();
            return false;
        }
        
        return $result;
    }
    /**
     * [remote 远程下载图片]
     * @param  string $url [description]
     * @return [type]      [description]
     */
    public function remote($url = '',$folder = 'common',$data = [],$path = false){
        if (empty($url)) {
            $this->error = "文件链接不能为空";
            return false;
        }
        try {
            $temp_dir = \Env::get('root_path') . 'public/uploads/temp/';
            if(!is_dir($temp_dir)){
                mkdir($temp_dir,0777,1);
            }
            $upload = Config::pull('upload');
            $type = strtolower(strrchr($url, '.'));
            preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $url, $info);
            $name = !empty($info) ? $info[1] : '';
            $context = \fast\Http::get($url);
            
            if($fp = @fopen($temp_dir.$name,'a')) {
                fwrite($fp,$context);
                fclose($fp);
            }else{
                throw new \Exception("临时文件写入失败", 1);
            }
            $suffix = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $suffix = $suffix ? $suffix : 'file';
            //判断是否已经存在附件
            $sha1 = hash_file('sha1', $temp_dir.$name);
            $uploaded = $this->model->where('sha1', $sha1)->find();

            $replaceArr = [
                '{year}'     => date("Y"),
                '{mon}'      => date("m"),
                '{day}'      => date("d"),
                '{hour}'     => date("H"),
                '{min}'      => date("i"),
                '{sec}'      => date("s"),
                '{folder}'   => $folder,
                '{random}'   => Random::alnum(16),
                '{random32}' => Random::alnum(32),
                '{filename}' => $name,
                '{suffix}'   => $suffix,
                '{.suffix}'  => $suffix ? '.' . $suffix : '',
                '{filemd5}'  => $sha1,
            ];
            $savekey = $upload['savekey'];
            $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

            $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
            $fileName = substr($savekey, strripos($savekey, '/') + 1);
            $uploadDir = str_replace('//','/',$uploadDir);
            if ($uploaded){
                if($path === true) $uploaded['upload_path'] = \Env::get('root_path') . 'public' . $uploadDir. $fileName;
                @unlink($temp_dir.$name);
                return $uploaded;
            }
            if(!is_dir(\Env::get('root_path') . '/public' . $uploadDir)){
                mkdir(\Env::get('root_path') . '/public' . $uploadDir,0777,1);
            }
            $splInfo = rename($temp_dir.$name,\Env::get('root_path') . '/public' . $uploadDir. $fileName);
            if (!$splInfo){
                throw new \Exception("文件移动失败", 1);
            }
            //保存数据库
            $url = $upload['siteurl'];
            $namearr = explode('.',$name);
            $params = array(
                'filesize'    => filesize(\Env::get('root_path') . '/public' . $uploadDir. $fileName),
                'imagewidth'  => '',
                'imageheight' => '',
                'name'   => $namearr[0],
                'imagetype'   => $suffix,
                'imageframes' => 0,
                'mimetype'    => trim($type,'.'),
                'url'         => $url . $uploadDir . $fileName,
                'uploadtime'  => time(),
                'storage'     => 'local',
                'sha1'        => $sha1,
            );

            if(!empty($data)){
                $params = array_merge($params,$data);
            }
            $result = $this->model->create(array_filter($params));
            if ($result === false){
                throw new \Exception($this->model->getError(), 1);
            }
            if($path === true) $result['upload_path'] = \Env::get('root_path') . 'public' . $uploadDir. $fileName;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return $result;
    }
    /**
     * 上传app
     */
    public function upload_app($file, $sub_domain){
        if (empty($file)) {
            $this->error = "请上传文件";
            return false;
        }
        //判断是否已经存在附件
        $sha1 = $file->hash();
        $uploaded = $this->model->where('sha1', $sha1)->find();
        if ($uploaded) return $uploaded;

        $upload = Config::pull('upload');
        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int) $upload['maxsize_app'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'file';

        $fileName = $fileInfo['name'];
        $fileName = substr_replace($fileName,".".$sub_domain,15,0);

        $uploadDir = $upload['upload_dir_app'];
        //验证文件 保存文件
        $splInfo = $file->validate(['size' => $size])->move(\Env::get('root_path') . '/public'.$uploadDir, $fileName);
        if (!$splInfo){
            $this->error = $file->getError();
            return false;
        }

        $imagewidth = $imageheight = 0;

        $url = $upload['siteurl'];
        $params = array(
            'filesize'    => $fileInfo['size'],
            'imagewidth'  => $imagewidth,
            'imageheight' => $imageheight,
            'name'   => substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')),
            'imagetype'   => $suffix,
            'imageframes' => 0,
            'mimetype'    => $fileInfo['type'],
            'url'         => $url . $uploadDir . $splInfo->getSaveName(),
            'uploadtime'  => time(),
            'storage'     => 'local',
            'sha1'        => $sha1,
        );

        if(!empty($data)){
            $params = array_merge($params,$data);
        }
        $result = $this->model->create(array_filter($params));
        if ($result === false){
            $this->error = $this->model->getError();
            return false;
        }
        return $result;
    }

    /**
     * 导出excel文件
     * @param  string $title  文件名-标题名
     * @param  array  $fields 字段配置
     *                    [
     *                        字段名称 => excel列名称
     *                    ]
     * @param  array $datas  需要导出的数据
     * @return mixed
     */
    public function export_excel($title='数据导出',$fields,$datas) {
        if (!$fields) {
            $this->error = lang('Parameter %s can not be empty',['(导出字段配置)']);
            return false;
        }

        if (!$datas) {
            $this->error = lang('Parameter %s can not be empty',['(导出数据)']);
            return false;
        }


        vendor("phpoffice.phpexcel.Classes.PHPExcel");
        vendor("phpoffice.phpexcel.Classes.PHPExcel.IOFactory");

        $phpexcel = new \PHPExcel();
        $phpexcel->setActiveSheetIndex(0)->setTitle($title);

        // $fields = [
        //     'name'             =>'姓名',
        //     'idcard'           => '身份证号',
        //     'sex'              => '性别',
        //     'nation'           => '民族',
        //     'native_place'     => '籍贯',
        //     'is_taiwan'        => '是否台湾省籍（1：是，2：否）',
        //     'birthday'         => '出生日期',
        //     'education'        => '学历',
        //     'type'             => '人员类型',
        //     'org.name'   => '党组织名称',
        //     'full_member_date' => '转正日期',
        //     'job'              => '工作岗位',
        //     'phone'            => '联系电话',
        //     'photo'            => '照片',
        //     'join_org_date'    => '入党时间'
        // ];

        $colums = [];
        $key = ord("A");
        $key2 = ord("@");
        foreach($fields as $field => $v) {
            if($key>ord("Z")){
                $key2 += 1;  
                $key = ord("A");  
                $colum = chr($key2).chr($key);//超过26个字母时才会启用 
            }else{  
                if($key2>=ord("A")){  
                    $colum = chr($key2).chr($key);  
                }else{  
                    $colum = chr($key);  
                }  
            }
            $colums[$field] = $colum;
            $key += 1;

            // 组装标题
            $phpexcel->setActiveSheetIndex(0)->setCellValue($colum.'1', $v);
        }

        // $datas = model('member/ccp_member','service')->lists([],false,0,'',true,['org']);
        $i = 2;
        $width = [];
        foreach ($datas as $data) {
            foreach ($colums as $field => $colum) {
                if (strpos($field, '.')) {
                    $arrs = explode('.', $field);

                    $obj = '';
                    foreach ($arrs as $k => $v) {
                        if ($k == 0) {
                            $obj = $data[$v];
                        } else {
                            $obj = $obj[$v];
                        }
                    }
                    $phpexcel->setActiveSheetIndex(0)->setCellValue($colum.$i, " ".$obj);


                    $width[$colum][$i] = mb_strwidth ($obj); //Return the width of the string
                } else {
                    $phpexcel->setActiveSheetIndex(0)->setCellValue($colum.$i, " ".$data[$field]);

                    $width[$colum][$i] = mb_strwidth ($data[$field]); //Return the width of the string
                }
            }
            $i++;
        }


        foreach($width as $k=>$v){
            $max_widthd = max($v)+4;
            $phpexcel->getActiveSheet()->getColumnDimension($k)->setWidth($max_widthd);
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
        // 从浏览器直接输出$filename
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-excel;");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=". $title .".xls");
        header("Content-Transfer-Encoding:binary");
        $objWriter->save("php://output");

        return true;
    }

    /**
     *删除
     */
    public function revoke($where){
        $maps['id'] = $where['id'];
        //查看是否有这条数据
        $is_has = $this->model->where($maps)->find();
        if(empty($is_has)){
            $this->error = lang('No results were found');
            \think\Db::rollback();
            return false;
        }

        $is_success = $this->model->where('id',$where['id'])->update(['delete_time'=>time()]);

        if($is_success === false){
            Db::rollback();
            $this->error = $this->getError();
            return false;
        }
        return true;
    }
    /**
     * 获取下载文件配置
     * @return [type] [description]
     */
    public function get_config()
    {
        $config = model('attachment/SysConfig');
        $data = $config->where(['group' => 'upload'])->cache('upload_config',3600)->select(); 
        return $data;
    }
    /**
     * 修改下载文件配置
     * @param array $params [0 => ['id' => 1,'name' => ''], 1 => []]
     */
    public function set_config($params = [])
    {
        if(empty($params)){
            $this->error = '没有修改内容';
            return false;
        }
        $config = model('attachment/SysConfig');
        $result = $config->saveAll($params);
        \Cache::rm('upload_config'); 
        return $result;
    }
}