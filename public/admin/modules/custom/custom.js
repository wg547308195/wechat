/**

 @Name：李勇 客户管理
 @Author：star1029
 @Site：http://www.layui.com/admin/
 @License：LPPL
    
 */


layui.define(['table', 'form'], function(exports){
  var $ = layui.$
  ,table = layui.table
  ,form = layui.form;

  //用户管理
  table.render({
    elem: '#LAY-custom-manage'
    ,url: '/admin/custom/index' //模拟接口
    ,cols: [[
      {field: 'username', title: '用户名', minWidth: 100}
      ,{field: 'mobile', title: '手机'}
      ,{field: 'area_name', title: '地区',align:'center', width: 200,templet:function (d) {
          if (d.area) {
              return d.area.merger_name.replace('中国,','');
          }
          return '-';
      }}
      ,{field: 'nickname', title: '昵称'}
      ,{field: 'email', title: '邮箱',width:200}
      ,{field: 'create_time', title: '创建时间',width:200, sort: true}
      ,{field: 'status', title:'状态', align:'center', width:120, templet: '#set-status', unresize: true}
      ,{title: '操作', width: 250, align:'center', fixed: 'right', toolbar: '#table-custom'}
    ]]
    ,page: true
    ,limit: 30
    ,height: 'full-220'
    ,text: '对不起，加载出现异常！'
    ,response: {
      statusName: 'code' //规定数据状态的字段名称，默认：code
      ,statusCode: 200 //规定成功的状态码，默认：0
      ,msgName: 'msg' //规定状态信息的字段名称，默认：msg
      ,countName: 'count' //规定数据总数的字段名称，默认：count
      ,dataName: 'data' //规定数据列表的字段名称，默认：data
    }
    ,parseData: function(res){ //res 即为原始返回的数据
      return {
        "code": res.code, //解析接口状态
        "msg": res.msg, //解析提示文本
        "count": res.data.total, //解析数据长度
        "data": res.data.data //解析数据列表
      };
    }
  });
  
  //监听工具条
  table.on('tool(LAY-custom-manage)', function(obj){
    var data = obj.data;
    if(obj.event === 'delete'){
      layer.confirm('确定删除吗', function(index){
          //ajax开始
            $.ajax({
              url: '/admin/custom/delete',
              type: "POST",
              data: {'id':data.id},
              dataType: 'json',
              success: function(res) {
                if (res.code == 0){
                  layer.msg(res.msg, {
                    offset: '15px'
                    ,icon: 2
                    ,anim: 6
                    ,time: 1000
                  }, function(){});
                  return false;
                }else{
                  layer.msg(res.msg, {
                    offset: '15px'
                    ,icon: 1
                    ,time: 1000
                  }, function(){
                    table.reload('LAY-custom-manage'); //数据刷新
                    layer.close(index); //关闭弹层
                  });
                }
                return false;
              }
            });
            return false;
        });
    } else if(obj.event === 'edit'){
      var tr = $(obj.tr);
      //将row对象赋给子页面
      window.ooob = data;
      layer.open({
        type: 2
        ,title: '编辑客户'
        ,content: '/admin/custom/edit'
        ,maxmin: true
        ,area: ['600px', '650px']
        ,btn: ['确定', '取消']
        ,yes: function(index, layero){
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submitID = 'LAY-custom-front-submit'
          ,submit = layero.find('iframe').contents().find('#'+ submitID);

          //监听提交
          iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
            var field = data.field; //获取提交的字段
            field.id = obj.data.id;

            //ajax开始
            $.ajax({
              url: '/admin/custom/edit',
              type: "POST",
              data: field,
              dataType: 'json',
              success: function(res) {
                if (res.code == 0){
                  layer.msg(res.msg, {
                    offset: '15px'
                    ,icon: 2
                    ,anim: 6
                    ,time: 1000
                  }, function(){});
                  return false;
                }else{
                  layer.msg(res.msg, {
                    offset: '15px'
                    ,icon: 1
                    ,time: 1000
                  }, function(){
                    table.reload('LAY-custom-manage'); //数据刷新
                    layer.close(index); //关闭弹层
                  });
                }
                return false;
              }
            });
            return false;
            //ajax结束
          });  
          
          submit.trigger('click');
        }
      });
    }
  });

  exports('custom/custom', {})
});