<?php
namespace app\admin\controller;

use think\Request;

class Upload extends Admin
{
    //编辑器图片上传
    public function upload_editor()
    {
        $uploadModel = new \app\attachment\service\Attachment();
        $file = $this->request->file('file');
        $result = $uploadModel->upload($file);
        if ($result !== false) {
            return $this->result(['src'=>$result->url],0,'上传成功');
        }
        return $this->result('',-1,$uploadModel->getError());
    }

    //图片上传
    public function upload(Request $request)
    {
        $uploadModel = new \app\attachment\service\Attachment();
        $file = $this->request->file('file');
        $result = $uploadModel->upload($file);
        if ($result !== false) {
            return $this->result($result->url,200,'上传成功');
        }
        return $this->result('',0,$uploadModel->getError());
    }
}