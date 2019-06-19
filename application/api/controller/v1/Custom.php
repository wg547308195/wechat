<?php
namespace app\api\controller\v1 ;
use app\api\controller\v1\Init;
class Custom extends Init
{

    public function initialize()
    {
        parent::initialize();
        $this->service = model('custom/SoCustom','service');
    }

    /**
     * [添加用户]
     * @return [type] [description]
     */
    public function create() {
        $data = $this->request->post();
        $result = $this->service->create($data);
        if(!$result) {
            return $this->response($this->service->getError(), [], -400104);
        }
        return $this->response("success", $result);
    }

    /**
     * [编辑用户]
     * @return [type] [description]
     */
    public function save() {
        $data = $this->request->post();
        if ($this->request->has('id','param',true) === false){
            return $this->response('id不能为空', [], -400105);
        }
        $result = $this->service->save($data);
        if (!$result){
            return $this->response($this->service->getError(), [], -400106);
        }
        return $this->response("success", $result);
    }

    /**
     * 获取管理员列表
     * @return array
     */
    public function lists() {
        $sqlmap = [];
        if (!empty($this->request->post('keywords'))){
            $keywords = $this->request->param('keywords','');
            $sqlmap[] = ['username','like', '%'.$keywords.'%'];
        }
        if ($this->request->has('group_id','param',true) === true){
            $sqlmap[] = ['group_id','=', $this->request->param('group_id',0)];
        }
        $sqlmap[] = ['site_code','like', $this->cur_site_code.'%'];
        $relations = (array) $this->request->post('relations/a');
        $result = $this->service->lists($sqlmap,$this->limit,$this->page,$this->order,'',$relations);
        if (!empty($result)){
            foreach($result as &$v){
                unset($v->password);
            }
        }
        return $this->response("success", $result);
    }

    /**
     * 软删除
     * @return mixed
     */
    public function destroy() {
        if ($this->request->has('id','param',true) === false){
            return $this->response('要删除的用户不能为空', [], -30103);
        }
        if ($this->request->post('id') == $this->user_id){
            return $this->response('非法操作!', [], -30103);
        }
        $result = $this->service->delete($this->request->post('id'));
        if (!$result) {
            return $this->response($this->service->getError(),[],-30103);
        }
        return $this->response('success',$result);
    }
    /**
     * [添加用户]
     * @return [type] [description]
     */
    public function wechat_create() {
        $data = $this->request->post();
        $result = $this->service->wechat_create($data);
        if(!$result) {
            return $this->response($this->service->getError(), [], -400104);
        }
        return $this->response("success", $result);
    }
    /**
     * 软删除
     * @return mixed
     */
    public function wechat_delete() {
        if ($this->request->has('openid','param',true) === false){
            return $this->response('要删除的用户不能为空', [], -30103);
        }
        $result = $this->service->wechat_delete($this->request->post('openid'));
        if (!$result) {
            return $this->response($this->service->getError(),[],-30103);
        }
        return $this->response('success',$result);
    }
    /**
     * 详细信息
     * @return array
     */
    public function wechat_detail() {

        if ($this->request->has('openid','param',true) === false){
            return $this->response('用户openid不能为空', [], -30103);
        }
    
        if ($this->request->has('openid','param',true) === false){
            return $this->response('要删除的用户不能为空', [], -30103);
        }
        $result = $this->service->wechat_detail($this->request->post('openid'));
        if (!$result) {
            return $this->response($this->service->getError(),[],-30103);
        }
        return $this->response('success',$result);
    }

}
