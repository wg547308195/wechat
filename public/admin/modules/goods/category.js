/**

 @Name：李勇 分类管理
 @Author：star1029
 @Site：http://www.layui.com/admin/
 @License：LPPL
    
 */


layui.define(['table', 'form'], function(exports){
  var $ = layui.$
  ,table = layui.table
  ,form = layui.form;

  table.render({
    elem: '#LAY-category-manage'
    ,url: '/admin/category/index' //模拟接口
    ,cols: [[
      {field: 'name', title: '名称'}
      // ,{field: 'cat_id', title: '上级分类', minWidth: 100}
      ,{field: 'sort', title: '排序', sort: true}
      ,{field: 'create_time', title: '创建时间',width:200, sort: true}
      ,{title: '操作', width: 250, align:'center', fixed: 'right', toolbar: '#table-category'}
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
  table.on('tool(LAY-category-manage)', function(obj){
    var data = obj.data;
    if(obj.event === 'delete'){
      layer.confirm('确定删除吗', function(index){
          //ajax开始
            $.ajax({
              url: '/admin/category/delete',
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
                    table.reload('LAY-category-manage'); //数据刷新
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
        ,title: '编辑分类'
        ,content: '/admin/category/edit'
        ,maxmin: true
        ,area: ['600px', '450px']
        ,btn: ['确定', '取消']
        ,yes: function(index, layero){
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submitID = 'LAY-category-front-submit'
          ,submit = layero.find('iframe').contents().find('#'+ submitID);

          //监听提交
          iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
            var field = data.field; //获取提交的字段
            field.id = obj.data.id;

            //ajax开始
            $.ajax({
              url: '/admin/category/edit',
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
                    table.reload('LAY-category-manage'); //数据刷新
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

  exports('goods/category', {})
});