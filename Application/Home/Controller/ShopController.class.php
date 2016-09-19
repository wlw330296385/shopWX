<?php

namespace Home\Controller;
use Think\Model;
use Think\Controller;

class ShopController extends BaseController
{
    public function index()
    {
        $this->display();
    }
    
    //CMS后台统计页面
    public function main()
    {
        //设置面包导航，主加载器请配置
        $bread = array(
            '0' => array(
                'name' => '主控面板',
                'url' => U('Admin/Index/main'),
            ),
        );
        $breadhtml = $this->getBread($bread);
        $this->assign('breadhtml', $breadhtml);

        //今日起始
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        $mapToday['ctime'] = array('between', array($beginToday, $endToday));
        //昨日起始
        $beginYesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
        $endYesterday = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
        $mapYesterday['ctime'] = array('between', array($beginYesterday, $endYesterday));
        //上周起始
        $beginLastweek = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
        $endLastweek = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('Y'));
        $mapLastweek['ctime'] = array('between', array($beginLastweek, $endLastweek));
        //本月起始
        $beginThismonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $endThismonth = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        $mapThismonth['ctime'] = array('between', array($beginThismonth, $endThismonth));

        //会员分布
        $mvip = M('Vip');
        $viptotal = $mvip->count();
        $vipsub = $mvip->where('subscribe=1')->count();
        $vipdissub = $viptotal - $vipsub;
        $this->assign('viptotal', $viptotal);
        $this->assign('vipsub', $vipsub);
        $this->assign('vipdissub', $vipdissub);
        //新会员
        $newvipToday = $mvip->where($mapToday)->count();
        $newvipYesterday = $mvip->where($mapYesterday)->count();
        //环比
        if ($newvipYesterday) {
            $newviprate = intval(($newvipToday - $newvipYesterday) / $newvipYesterday * 100);
        } else {
            $newviprate = $newvipToday * 100;
        }
        //总共
        if ($viptotal) {
            $newviptotalrate = intval($newvipToday / $viptotal * 100);
        } else {
            $newviptotalrate = $newvipToday * 100;
        }

        $this->assign('newvipToday', $newvipToday);
        $this->assign('newvipYesterday', $newvipYesterday);
        $this->assign('newviprate', $newviprate);
        $this->assign('newviptotalrate', $newviptotalrate);
        //dump($mapToday);
        //订单分布
        $morder = M('Shop_order');
        $ordertotal = $morder->count();
        $this->assign('ordertotal', $ordertotal);
        for ($i = 0; $i < 7; $i++) {
            $name = 'order' . $i;
            $num = $morder->where('status=' . $i)->count();
            $this->assign($name, $num);
        }
        //订单
        $neworderToday = $morder->where($mapToday)->count();
        $neworderYesterday = $morder->where($mapYesterday)->count();
        //环比
        if ($neworderYesterday) {
            $neworderrate = intval(($neworderToday - $neworderYesterday) / $neworderYesterday * 100);
        } else {
            $neworderrate = $neworderToday * 100;
        }
        //总共
        if ($ordertotal) {
            $newordertotalrate = intval($neworderToday / $ordertotal * 100);
        } else {
            $newordertotalrate = $neworderToday * 100;
        }

        $this->assign('neworderToday', $neworderToday);
        $this->assign('neworderYesterday', $neworderYesterday);
        $this->assign('neworderrate', $neworderrate);
        $this->assign('newordertotalrate', $newordertotalrate);

        //佣金分析
        //订单分布
        $myj = M('Fx_syslog');
        $yjtotal = $myj->sum('fxyj');
        //今日佣金
        $yjToday = number_format($myj->where($mapToday)->sum('fxyj'), 2);
        $yjYesterday = number_format($myj->where($mapYesterday)->sum('fxyj'), 2);
        if (!$yjToday) {
            $yjToday = 0;
        }
        if (!$yjYesterday) {
            $yjYesterday = 0;
        }
        //环比
        if ($yjYesterday) {
            $yjrate = intval(($yjToday - $yjYesterday) / $yjYesterday * 100);
        } else {
            $yjrate = $yjrate * 100;
        }
        //总共
        if ($yjtotal) {
            $yjtotalrate = intval($yjToday / $yjtotal * 100);
        } else {
            $yjtotalrate = $yjToday * 100;
        }
        $this->assign('yjtotal', $yjtotal);
        $this->assign('yjToday', $yjToday);
        $this->assign('yjYesterday', $yjYesterday);
        $this->assign('yjrate', $yjrate);
        $this->assign('yjtotalrate', $yjtotalrate);
        //分销分布
        //普通会员
        $fx1 = $mvip->where(array('isfx' => 0, 'isfxgd' => 0))->count();
        //分销商
        $fx2 = $mvip->where(array('isfx' => 1, 'isfxgd' => 0))->count();
        //超级VIP
        $fx3 = $mvip->where('isfxgd=1')->count();
        $this->assign('fx1', $fx1);
        $this->assign('fx2', $fx2);
        $this->assign('fx3', $fx3);

        //会员关注日志
        $viplog = M('Vip_log_sub')->limit(5)->order('ctime desc')->select();
        foreach ($viplog as $k => $v) {
            $tmpvip = $mvip->where('id=' . $v['from'])->find();
            $viplog[$k]['headimgurl'] = $tmpvip['headimgurl'];
            $viplog[$k]['sex'] = $tmpvip['sex'] == 1 ? '男' : '女';
            $viplog[$k]['country'] = $tmpvip['country'];
            $viplog[$k]['province'] = $tmpvip['province'];
            $viplog[$k]['city'] = $tmpvip['city'];
            $viplog[$k]['event'] = $v['issub'] ? '关注' : '取消关注';
        }
        $this->assign('viplog', $viplog);
        //会员分销日志
        $fxlog = M('Fx_syslog')->limit(5)->order('ctime desc')->select();
        foreach ($fxlog as $k => $v) {
            $tmpvip = $mvip->where('id=' . $v['from'])->find();
            $fxlog[$k]['headimgurl'] = $tmpvip['headimgurl'];
            $fxlog[$k]['sex'] = $tmpvip['sex'] == 1 ? '男' : '女';
            $fxlog[$k]['event'] = $v['status'] ? '派发成功' : '派发失败';
        }
        $this->assign('fxlog', $fxlog);
        //会员推广日志
        $tjlog = M('Fx_log_tj')->limit(5)->order('ctime desc')->select();
        foreach ($tjlog as $k => $v) {
            $tmpvip = $mvip->where('id=' . $v['vipid'])->find();
            $tjlog[$k]['headimgurl'] = $tmpvip['headimgurl'];
            $tjlog[$k]['sex'] = $tmpvip['sex'] == 1 ? '男' : '女';
            $tjlog[$k]['event'] = '下线推广';
        }
        $this->assign('tjlog', $tjlog);
        //订单支付日志
        $orderlog = M('Shop_order')->where(array('ispay' => 1, 'status' => 2))->limit(5)->order('ctime desc')->select();
        foreach ($orderlog as $k => $v) {
            $tmpvip = $mvip->where('id=' . $v['vipid'])->find();
            $orderlog[$k]['headimgurl'] = $tmpvip['headimgurl'];
            $orderlog[$k]['sex'] = $tmpvip['sex'] == 1 ? '男' : '女';
        }
        $this->assign('orderlog', $orderlog);
        //汇总统计
        //总销售额
        $zxse = number_format($morder->where(array('ispay' => 1))->sum('totalprice'), 2);
        //总成交额
        $zcje = number_format($morder->where(array('status' => 5))->sum('totalprice'), 2);
        //总销量
        $zxl = $morder->where(array('ispay' => 1))->sum('totalnum');
        //总佣金
        $zyj = number_format($yjtotal, 2);
        $ztx = M('Vip_tx')->where(array('status' => 2))->sum('txprice');
        $this->assign('zxse', $zxse);
        $this->assign('zcje', $zcje);
        $this->assign('zxl', $zxl);
        $this->assign('zyj', $zyj);
        $this->assign('ztx', $ztx);
        //计算系统运行时间
        $datetime1 = date_create('2015-1-25');
        $datetime2 = date_create(date('Y-m-d', time()));
        $interval = date_diff($datetime1, $datetime2);
        $remaindays = $interval->format('%a');
        $this->assign('remaindays', $remaindays);
        $this->display();
    }
    public function getKd()
    {
        header("Content-type:text/html;charset=utf-8");
        if (!$_GET['fahuokd'] || !$_GET['fahuokdnum']) {
            $ret = array(
                'status' => 0,
                'msg' => '缺少参数',
            );
            $this->ajaxReturn($ret);
        }
        $kd = urldecode(I('get.fahuokd'));
        switch ($kd) {
            case '百世快递':
                $kdname = 'huitongkuaidi';
                break;
            case '中通':
                $data = json_encode(array(I('get.fahuokdnum')));
                $company_id = '17b972146c8148b38c67a04a8b116cba';
                $sign = 'E8B34840D863229E4BD057FDC73B8EA1';
                $digest = md5($data.$sign);
                $toUrl = 'http://japi.zto.cn/zto/api_utf8/traceInterface?'.'data='.$data.'&data_digest='.$digest.'&msg_type=TRACES&company_id='.$company_id;
                $resData = http_get($toUrl);
                echo $resData;
                exit;
                break;
            default:
                $ret = array(
                    'status' => 0,
                    'msg' => '参数错误',
                );
                $this->ajaxReturn($ret);
                break;
        }
        // 查询
        $url = 'http://api.kuaidi100.com/api?id=4c52ef78aa98a65d&com='.$kdname.'&nu='.I('get.fahuokdnum').'&show=0&muti=1&order=desc';
        // $this->ajaxReturn();
        $data = http_get($url);
        echo($data);
        exit;
    }
    //数据统计
    public function Count(){
        //设置面包导航，主加载器请配置
        $bread = array(
            '0' => array(
                'name' => '商城首页',
                'url' => U('Home/Shop/index'),
            ),
            '1' => array(
                'name' => '数据统计',
                'url' => $id ? U('Home/Shop/Count', array('id' => $id)) : U('Home/Shop/Count'),
            ),
        );
        $this->assign('breadhtml', $this->getBread($bread));
        //绑定筛选时间
        $parameter=I('path.');
        $ctime = $_GET['ctime'] ? $_GET['ctime'] :'';
        if ($ctime != '') {
            $timeArr = explode(" - ", $ctime);
            $optime = strtotime($timeArr[0]);
            $edtime = strtotime($timeArr[1]);
            $map['ctime']   = array('BETWEEN',array($optime,$edtime));              
        }
        if (strlen($parameter[3])==23) {
            $timeArr = explode("+-+", $parameter[3]);
            $optime = strtotime($timeArr[0]);
            $edtime = strtotime($timeArr[1]);
            $map['ctime']   = array('BETWEEN',array($optime,$edtime));
        }
        //订单数据
        $orderData=M('shop_syslog_sells')->where($map)->field('goodsname,price,sum(total) as sum,sum(num) as num')->group('price')->select();
        $this->assign('orderData',$orderData);
         //会员数据
        $map['issub']=1;
        $regi=M('vip_log_sub')->where($map)->count();
        $map['issub']=0;
        $quit=M('vip_log_sub')->where($map)->count();
        $map=1;
        $login=M('vip_log')->where($map)->count();
        $this->assign('regi',$regi);
        $this->assign('quit',$quit);
        $this->assign('login',$login);
        if (!$ctime) {
            $ctime='全部时间';
        }
        $this->assign('ctime',$ctime);
        $this->display();
    }
    public function vipCount(){
        //设置面包导航，主加载器请配置
        $bread = array(
            '0' => array(
                'name' => '商城首页',
                'url' => U('Home/Shop/index'),
            ),
            '1' => array(
                'name' => '会员统计',
                'url' => $id ? U('Home/Shop/vipCount', array('id' => $id)) : U('Home/Shop/vipCount'),
            ),
        );
        $this->assign('breadhtml', $this->getBread($bread));
        //绑定筛选时间
        $parameter=I('path.');
        $ctime = $_GET['ctime'] ? $_GET['ctime'] :'';
        if ($ctime != '') {
            $timeArr = explode(" - ", $ctime);
            $optime = strtotime($timeArr[0]);
            $edtime = strtotime($timeArr[1]);
            $map['ctime']   = array('BETWEEN',array($optime,$edtime));              
        }
        if (strlen($parameter[3])==23) {
            $timeArr = explode("+-+", $parameter[3]);
            $optime = strtotime($timeArr[0]);
            $edtime = strtotime($timeArr[1]);
            $map['ctime']   = array('BETWEEN',array($optime,$edtime));
        }
         //会员数据
        $map['issub']=1;
        $regi=M('vip_log_sub')->where($map)->count();
        $map['issub']=0;
        $quit=M('vip_log_sub')->where($map)->count();
        $map=1;
        $login=M('vip_log')->where($map)->count();
        $this->assign('regi',$regi);
        $this->assign('quit',$quit);
        $this->assign('login',$login);
          if (!$ctime) {
            $ctime='全部时间';
        }
        $this->assign('ctime',$ctime);
        $this->display();
    }

    public function orderCountExport(){
        $status = I('status');
        if ($status) {
           $map['status'] = $status;
               switch ($status) {
                case 0:
                    $tt = "交易取消";
                    break;
                case 1:
                    $tt = "未付款";
                    break;
                case 2:
                    $tt = "已付款";
                    break;
                case 3:
                    $tt = "已发货";
                    break;
                case 4:
                    $tt = "退货中";
                    break;
                case 7:
                    $tt = "退货完成";
                    break;
                case 5:
                    $tt = "交易成功";
                    break;
                case 6:
                    $tt = "交易关闭";
                    break;
                default:
                    $tt='全部订单';
                    break;
            }
        }else{
            $map=1;
            $tt='全部订单';
        }
        $data = M('Shop_order')->where($map)->select();
        foreach ($data as $k => $v) {
            //过滤字段
            unset($data[$k]['sid']);
            unset($data[$k]['ispay']);
            unset($data[$k]['kfmsg']);
            unset($data[$k]['vipxqname']);
            unset($data[$k]['vipxqid']);
            unset($data[$k]['ntime']);
            unset($data[$k]['dtime']);
            unset($data[$k]['etime']);
            switch ($v['status']) {
                case 0:
                    $data[$k]['status'] = "交易取消";
                    break;
                case 1:
                    $data[$k]['status'] = "未付款";
                    break;
                case 2:
                    $data[$k]['status'] = "已付款";
                    break;
                case 3:
                    $data[$k]['status'] = "已发货";
                    break;
                case 4:
                    $data[$k]['status'] = "退货中";
                    break;
                case 7:
                    $data[$k]['status'] = "退货完成";
                    break;
                case 5:
                    $data[$k]['status'] = "交易成功";
                    break;
                case 6:
                    $data[$k]['status'] = "交易关闭";
                    break;
            }
            $data[$k]['ctime'] = date('Y-m-d H:i:s', $v['ctime']);
            $data[$k]['paytime'] = $v['paytime'] ? date('Y-m-d H:i:s', $v['paytime']) : '无';
            $data[$k]['changetime'] = $v['changetime'] ? date('Y-m-d H:i:s', $v['changetime']) : '无';
            $data[$k]['closetime'] = $v['closetime'] ? date('Y-m-d H:i:s', $v['closetime']) : '无';
            $data[$k]['tuihuosqtime'] = $v['tuihuosqtime'] ? date('Y-m-d H:i:s', $v['tuihuosqtime']) : '无';
            $data[$k]['tuihuotime'] = $v['tuihuotime'] ? date('Y-m-d H:i:s', $v['tuihuotime']) : '无';
            $tmpitems = unserialize($v['items']);
            $str = "";
            foreach ($tmpitems as $vv) {
                $vt = '品名：' . $vv['name'] . ' 属性：' . $vv['skuattr'] . '数量：' . $vv['num'] . '单价：' . $vv['price'];
                $str = $str . $vt . '/***/';
            }
            $data[$k]['items'] = $str;
        }
        //dump($data);
        //die();
        $title = array('ID', '订单编号', '代金卷ID', '订单总价', '商品总数', '支付价格', '支付类型', '支付时间', '支付宝支付帐号', '邮费', '会员ID', '会员微信ID', '收货姓名', '收货电话', '收货地址', '购买留言', '订单创建时间', '改价时间', '改价原因', '改价操作员', '关闭时间', '关闭原因', '关闭操作员', '退货退款金额', '退货退款申请时间', '退货退款完成时间', '退货快递公司', '退货快递单号', '退货原因', '退货操作员', '订单状态', '发货快递', '发货快递号', '订单商品详情');
        $this->exportexcel($data, $title, $tt . '订单' . date('Y-m-d H:i:s', time()));

    }

    public function chart(){        
        $this->display(); 
    }
    public function getcharts(){
    //最近三十天内交易数据: 
        for($i=0;$i<30;$i++){
            $days[]= date("Y-m-d", strtotime('-'. $i . 'day'));
        }
        sort($days);
        //三十天内完成的订单
        $data['orderDone']=M('shop_order')
            ->group('days')
            ->order('days desc')
            ->field("FROM_UNIXTIME(etime,'%Y-%m-%d') days,COUNT(oid) c")
            ->where('status=5')
            ->limit(30)
            ->select();
        $data['orderDone']['totalnum']=M('shop_order')
            ->field("FROM_UNIXTIME(etime,'%Y-%m-%d') days,COUNT(oid) c")
            ->where('status=5')
            ->limit(30)
            ->sum('totalnum');
        //三十天内已付款订单
        $data['orderPay']=M('shop_order')
            ->group('days')
            ->order('days desc')
            ->field("FROM_UNIXTIME(paytime,'%Y-%m-%d') days,COUNT(oid) c")
            ->where('status=2')
            ->limit(30)
            ->select();
        $data['orderPay']['totalnum']=M('shop_order')
            ->field("FROM_UNIXTIME(etime,'%Y-%m-%d') days,COUNT(oid) c")
            ->where('status=2')
            ->limit(30)
            ->sum('totalnum');    
        // 三十天内交易关闭
        $data['orderClose']=M('shop_order')
            ->group('days')
            ->order('days desc')
            ->field("FROM_UNIXTIME(closetime,'%Y-%m-%d') days,COUNT(oid) c")
            ->where('status=6')
            ->limit(30)
            ->select();
         $data['orderClose']['totalnum']=M('shop_order')
            ->field("FROM_UNIXTIME(etime,'%Y-%m-%d') days,COUNT(oid) c")
            ->where('status=6')
            ->limit(30)
            ->sum('totalnum'); 
        //三十天内未付款
        $data['orderNotpay']=M('shop_order')
            ->group('days')
            ->order('days desc')
            ->field("FROM_UNIXTIME(ctime,'%Y-%m-%d') days,COUNT(oid) c")
            ->where('status=1')
            ->limit(30)
            ->select();
        $data['orderNotpay']['totalnum']=M('shop_order')
            ->field("FROM_UNIXTIME(etime,'%Y-%m-%d') days,COUNT(oid) c")
            ->where('status=1')
            ->limit(30)
            ->sum('totalnum'); 
        // 三十天内退货 
        $data['orderReturn']=M('shop_order')
            ->group('days')
            ->order('days desc')
            ->field("FROM_UNIXTIME(ctime,'%Y-%m-%d') days,COUNT(oid) c")
            ->where('status=4')
            ->limit(30)
            ->select();
        $data['orderReturn']['totalnum']=M('shop_order')
            ->field("FROM_UNIXTIME(etime,'%Y-%m-%d') days,COUNT(oid) c")
            ->where('status=4')
            ->limit(30)
            ->sum('totalnum');
        foreach ($days as $key => $value) {
            $reg1=0;$reg2=0;$reg3=0;$reg4=0;$reg5=0;
            $data['orderDone']['days'][]=$value;
            $data['orderClose']['days'][]=$value;
            $data['orderNotpay']['days'][]=$value;
            $data['orderReturn']['days'][]=$value;
            foreach ($data['orderDone'] as $k => $val) {
                if ($val['days']==$value) {
                    $reg1=$val['c'];
                }
            }$data['orderDone']['reg'][]=$reg1;
            foreach ($data['orderClose'] as $k => $val) {
                if ($val['days']==$value) {
                    $reg2=$val['c'];
                }  
            }$data['orderClose']['reg'][]=$reg2;
            foreach ($data['orderNotpay'] as $k => $val) {
                if ($val['days']==$value) {
                    $reg3=$val['c'];
                }  
            }
            $data['orderNotpay']['reg'][]=$reg3;
            foreach ($data['orderReturn'] as $k => $val) {
                if ($val['days']==$value) {
                    $reg4=$val['c'];
                } 
            }
            $data['orderReturn']['reg'][]=$reg4;
            foreach ($data['orderPay'] as $k => $val) {
                if ($val['days']==$value) {
                    $reg1=$val['c'];
                }
            }$data['orderPay']['reg'][]=$reg5;
        }
        $data['orderDone']['totalnum']=$data['orderDone']['totalnum']?$data['orderDone']['totalnum']:0;
        $data['orderReturn']['totalnum']=$data['orderReturn']['totalnum']?$data['orderReturn']['totalnum']:0;
        $data['orderNotpay']['totalnum']=$data['orderNotpay']['totalnum']?$data['orderNotpay']['totalnum']:0;
        $data['orderClose']['totalnum']=$data['orderClose']['totalnum']?$data['orderClose']['totalnum']:0;
        $data['orderPay']['totalnum']=$data['orderPay']['totalnum']?$data['orderPay']['totalnum']:0;
        if (IS_AJAX) {
                $this->ajaxReturn($data);
            }   
    }

    public function getVipcharts(){
        $day=30;
        //30天内的关注数;
        $data['sub']=M('vip_log_sub')->group('days')
            ->order('days desc')
            ->field("FROM_UNIXTIME(ctime,'%Y-%m-%d') days,COUNT(id) c")
            ->where('issub=1')
            ->limit($day)
            ->select();
        $data['sub']['totalnum']=M('vip_log_sub')
            ->field("FROM_UNIXTIME(ctime,'%Y-%m-%d') days")
            ->where('issub=1')
            ->limit($day)
            ->count();
        // 30天内取消关注量
        $data['unsub']=M('vip_log_sub')->group('days')
            ->order('days desc')
            ->field("FROM_UNIXTIME(ctime,'%Y-%m-%d') days,COUNT(id) c")
            ->where('issub=0')
            ->limit($day)
            ->select();
        $data['unsub']['totalnum']=M('vip_log_sub')
            ->field("FROM_UNIXTIME(ctime,'%Y-%m-%d') days")
            ->where('issub=0')
            ->limit($day)
            ->count();
        for($i=0;$i<$day;$i++){
            $days[]= date("Y-m-d", strtotime('-'. $i . 'day'));
        }
        sort($days);
        foreach ($days as $key => $value) {
            $data['days'][]=$value;
            $reg1=0;$reg2=0;
            foreach ($data['sub'] as $k => $val) {
                if ($val['days']==$value) {
                    $reg1=$val['c'];
                }
            }
            $data['sub']['reg'][]=$reg1;
            foreach ($data['unsub'] as $k => $val) {
                 if ($val['days']==$value) {
                    $reg2=$val['c'];
                }
            }
            $data['unsub']['reg'][]=$reg2;
        }
       if (IS_AJAX) {
                $this->ajaxReturn($data);
            }  
    }

    public function getVipbuy(){
        $Model= new Model();
        $vipbuysql=$Model->query("select b.a,count(b.a) AS buynum FROM (SELECT COUNT(`vipid`) as a FROM `wfx_shop_syslog_sells` GROUP BY `vipid`) b GROUP BY `a`");
        $data=array(
            'one'=>$vipbuysql[0]['buynum']*$vipbuysql[0]['a'],
            'two'=>$vipbuysql[1]['buynum']*$vipbuysql[1]['a'],
            'three'=>$vipbuysql[2]['buynum']*$vipbuysql[1]['a']
            );

        foreach ($vipbuysql as $key => $value) {
            if ($key>=3) {
                $data['more']+=$value['a']*$value['buynum'];
            }
        }
        if (IS_AJAX) {
            $this->ajaxReturn($data);
        }
    }
     public function vipList()
    {
        //设置面包导航，主加载器请配置
        $bread = array(
            '0' => array(
                'name' => '会员中心',
                'url' => U('Admin/Normal/vipList'),
            ),
            '1' => array(
                'name' => '会员列表',
            ),
        );
        $this->assign('breadhtml', $this->getBread($bread));
        // 员工介入
        $temp = M('employee')->select();
        $employee = array();
        foreach ($temp as $k => $v) {
            $employee[$v['id']] = $v;
        }
        //绑定搜索条件与分页
        $m = M('Vip');
        $p = $_GET['p'] ? $_GET['p'] : 1;
        $search = I('search') ? I('search') : '';
        if ($search) {
            $map['nickname'] = array('like', "%$search%");
            $map['mobile'] = array('like', "%$search%");
            $map['_logic'] = 'OR';
            $this->assign('search', $search);
        }
        $psize =  20;
        $cache = $m->where($map)->page($p, $psize)->select();
        foreach ($cache as $k => $v) {
            $cache[$k]['levelname'] = M('vip_level')->where('id=' . $cache[$k]['levelid'])->getField('name');
            if ($v['isfxgd']) {
                $cache[$k]['fxname'] = '超级VIP';
            } else {
                if ($v['isfx']) {
                    $cache[$k]['fxname'] = $_SESSION['SHOP']['set']['fxname'];
                } else {
                    $cache[$k]['fxname'] = '会员';
                }
            }
            // 写入员工数据
            if ($v['employee']) {
                $cache[$k]['employee'] = $employee[$v['employee']]['nickname'];
            } else {
                $cache[$k]['employee'] = '无';
            }
        }
        $count = $m->where($map)->count();
        $this->getPage($count, $psize, 'App-loader', '会员列表', 'App-search');
        $this->assign('cache', $cache);
        $this->display();
    }
}
