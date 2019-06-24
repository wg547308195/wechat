/**

 @Name：李勇 用户管理
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
    elem: '#LAY-user-manage'
    ,url: '/admin/user/index' //模拟接口
    ,cols: [[
      {field: 'nickname', title: '昵称'}
      ,{field: 'mobile', title: '手机'}
      ,{field: 'headimgurl', title: '头像',width:100, align: 'center', templet: '#imgTpl'}
      ,{field: 'province', title: '省份'}
      ,{field: 'city', title: '城市'}
      ,{field: 'subscribe', title: '是否关注',align: 'center',templet: '#buttonTpl', minWidth: 80}
      ,{field: 'subscribe_time', title: '关注时间',minWidth: 120}
      ,{field: 'sex', title: '性别',templet: function(d){
        return d.sex == 0 ? '未知' : (d.sex == 1 ? '男' : '女');
      }}
      ,{field: 'custom_name', title: '经销商',width:200,'templet':function (d) {
          if (d.custom) {
              return d.custom.nickname;
          }
          return '-';
      }}
      ,{field: 'create_time', title: '创建时间',width:200, sort: true}
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

  exports('user/user', {})
});