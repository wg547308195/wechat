/**
 @Name：产品管理
 @Author：star1029
 @Site：http://www.layui.com/admin/
 @License：LPPL
    
 */
layui.define(['table', 'form', 'upload'], function(exports){
  var $ = layui.$
  ,table = layui.table
  ,form = layui.form
  ,admin = layui.admin
  ,site_url = window.location.search
  ,upload = layui.upload;
  //产品管理
  table.render({
    elem: '#LAY-goods-manage'
    ,url: '/admin/goods/index' + site_url//模拟接口
    ,cols: [[
      {field: 'id', title: 'ID', width: 200}      
      ,{field: 'name', title: '名称' ,minWidth: 100}
      ,{field: 'cat_id', title: '分类', align:'center', width: 120,templet:function (d) {
          if (d.category) {
              return d.category.name;
          }
          return '-';
      }}
      ,{field: 'img', title: '图片', align:'center', width: 100, templet: '#imgTpl'}
      ,{field: 'time_length', title: '保修期(天)', align:'center', sort: true,width: 160}
      ,{field: 'status', title: '状态', align:'center',templet: "#buttonTpl",sort: true, width: 160}
      ,{field: 'create_time', title: '创建时间', sort: true, width: 160}
      ,{title: '操作', width: 250, align:'center', fixed: 'right', toolbar: '#table-useradmin-webuser'}
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
  table.on('tool(LAY-goods-manage)', function(obj){
    var data = obj.data;
    if(obj.event === 'delete'){
        layer.confirm('确定删除吗', function(index){
          //ajax开始
            $.ajax({
              url: '/admin/goods/delete',
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
                    table.reload('LAY-goods-manage'); //数据刷新
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
      var edit_index = layer.open({
        type: 2
        ,title: '编辑产品'
        ,content: '/admin/goods/edit'
        ,maxmin: true
        ,area: ['700px', '800px']
        ,btn: ['确定', '取消']
        ,btnAlign: 'l'
        ,yes: function(index, layero){
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submitID = 'LAY-goods-front-submit'
          ,submit = layero.find('iframe').contents().find('#'+ submitID);

          //监听提交
          iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
            var field = data.field; //获取提交的字段
            field.id = obj.data.id;
            //ajax开始
            $.ajax({
              url: '/admin/goods/edit',
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
                    table.reload('LAY-goods-manage'); //数据刷新
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
      layer.full(edit_index);
    }
  });

  //上传图片
  var imgsSrc = $('#LAY_imgSrc');
  upload.render({
    url: '/admin/upload/upload'
    ,elem: '#LAY_imgUpload'
    ,field: 'file'
    ,done: function(res){
      if(res.code == 200){
        imgsSrc.val(res.data);
      } else {
        layer.msg(res.msg, {icon: 5});
      }
    }
  });
  
  //查看图片
  admin.events.avartatPreview = function(othis){
    var src = imgsSrc.val();
    layer.photos({
      photos: {
        "title": "查看图片" //相册标题
        ,"data": [{
          "src": src //原图地址
        }]
      }
      ,shade: 0.01
      ,closeBtn: 1
      ,anim: 5
    });
  };
  exports('goods/goods', {})
});