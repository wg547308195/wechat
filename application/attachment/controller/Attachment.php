<?php

namespace app\attachment\controller;

use app\admin\controller\Backend;

/**
 * 附件管理
 *
 * @icon fa fa-circle-o
 * @remark 主要用于管理上传到又拍云的数据或上传至本服务的上传数据
 */
class Attachment extends Backend
{

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('attachment/ccp_attachment', 'service');
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'htmlspecialchars']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();              
            $result = $this->model->lists($where, $limit, $offset, $sort.' '.$order);
            $cdnurl = preg_replace("/\/(\w+)\.php$/i", '', $this->request->root());
            foreach ($result as $k => &$v)
            {
                $v['fullurl'] = ($v['storage'] == 'local' ? $cdnurl : $this->view->config['upload']['cdnurl']) . $v['url'];
            }
            return $this->success('Operation completed', null, $result);
        }
        return $this->view->fetch();
    }

    /**
     * 选择附件
     */
    public function select()
    {
        if ($this->request->isAjax())
        {
            return $this->index();
        }
        return $this->view->fetch();
    }

    //附件上传
    public function upload(){
        $file = $this->request->file('file');
        $result = $this->model->upload($file, $this->request->param('folder'));
        if ($result === false){
            $this->error($this->model->getError());
        }
        $this->success('上传成功', $result['url'], $result);
    }

}
