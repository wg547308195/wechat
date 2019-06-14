/**

 @Name：layuiAdmin 主页控制台
 @Author：贤心
 @Site：http://www.layui.com/admin/
 @License：GPL-2

 */


layui.define(function(exports){

  //区块轮播切换
  layui.use(['admin', 'carousel'], function(){
    var $ = layui.$
    ,admin = layui.admin
    ,carousel = layui.carousel
    ,element = layui.element
    ,device = layui.device();

    //轮播切换
    $('.layadmin-carousel').each(function(){
      var othis = $(this);
      carousel.render({
        elem: this
        ,width: '100%'
        ,arrow: 'none'
        ,interval: othis.data('interval')
        ,autoplay: othis.data('autoplay') === true
        ,trigger: (device.ios || device.android) ? 'click' : 'hover'
        ,anim: othis.data('anim')
      });
    });

    element.render('progress');

  });

  //数据概览
  layui.use(['admin', 'carousel', 'echarts'], function(){
    var $ = layui.$
    ,admin = layui.admin
    ,carousel = layui.carousel
    ,echarts = layui.echarts;

    var echartsApp = [], options = [
      //新增用户走势
      {
        title: {
          text: '新增用户走势 （半月）',
          x: 'center',
          textStyle: {
            fontSize: 14
          }
        },
        tooltip : { //提示框
          trigger: 'axis',
          formatter: "{b}<br>新增用户：{c}"
        },
        xAxis : [{ //X轴
          type : 'category',
          data : ['11-07', '11-08', '11-09', '11-10', '11-11', '11-12', '11-13']
        }],
        yAxis : [{  //Y轴
          type : 'value'
        }],
        series : [{ //内容
          type: 'line',
          data:[200, 300, 400, 610, 150, 270, 380],
        }]
      },
      //新增经销商走势
      {
        title: {
          text: '新增经销商走势 （半月）',
          x: 'center',
          textStyle: {
            fontSize: 14
          }
        },
        tooltip : { //提示框
          trigger: 'axis',
          formatter: "{b}<br>新增经销商：{c}"
        },
        xAxis : [{ //X轴
          type : 'category',
          data : ['11-07', '11-08', '11-09', '11-10', '11-11', '11-12', '11-13']
        }],
        yAxis : [{  //Y轴
          type : 'value'
        }],
        series : [{ //内容
          type: 'line',
          data:[400, 300, 200, 610, 150, 270, 380],
        }]
      }
    ]

  ,elemDataView = $('#LAY-index-dataview').children('div')
    ,renderDataView = function(index){
      echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
      echartsApp[index].setOption(options[index]);
      //window.onresize = echartsApp[index].resize;
      admin.resize(function(){
        echartsApp[index].resize();
      });
    };

    //没找到DOM，终止执行
    if(!elemDataView[0]) return;

    $.getJSON('/admin/index/ajax_home',{},function (e) {
        if (e.code == 1) {
              if (e.info) {
                  /* 新增用户走势 */
                  if (e.info.dates) {
                      options[0].xAxis[0].data = e.info.dates;
                  }
                  if (e.info.user) {
                      options[0].series[0].data = e.info.user;
                  }
                  /* 新增经销商走势 */
                  if (e.info.dates) {
                      options[1].xAxis[0].data = e.info.dates;
                  }
                  if (e.info.custom) {
                      options[1].series[0].data = e.info.custom;
                  }
              }
              renderDataView(0);
        }
    },'json');

    renderDataView(0);

    //监听数据概览轮播
    var carouselIndex = 0;
    carousel.on('change(LAY-index-dataview)', function(obj){
      renderDataView(carouselIndex = obj.index);
    });

    //监听侧边伸缩
    layui.admin.on('side', function(){
      setTimeout(function(){
        renderDataView(carouselIndex);
      }, 300);
    });

    //监听路由
    layui.admin.on('hash(tab)', function(){
      layui.router().path.join('') || renderDataView(carouselIndex);
    });
  });

  //经销商用户排名
  layui.use('table', function(){
    var $ = layui.$
    ,table = layui.table;
    
    table.render({
      elem: '#LAY-index-topCustom'
      ,url: '/admin/index/ajax_top_custom'
      ,page: true
      ,cols: [[
        {field: 'username', title: '用户名', minWidth: 120}
        ,{field: 'nickname', title: '昵称', minWidth: 120}
        ,{field: 'count', title: '用户数', sort: true}
      ]]
      ,skin: 'line'
    });
  });
  
  //对外暴露的接口
  exports('index/home', {});
});