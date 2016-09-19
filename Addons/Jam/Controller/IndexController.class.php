<?php
/**
 * Created by PhpStorm.
 * User: heqing
 * Date: 15/7/30
 * Time: 09:40.
 */
namespace Addons\Jam\Controller;

class IndexController extends InitController
{
    public $appUrl = '';
    public $jam_config = array();
    public function __construct()
    {
        header('Content-type: text/html; charset=utf-8');
        parent::__construct();
        $this->appUrl = 'http://'.I('server.HTTP_HOST');
        $this->jam_config = M('jam_config')->find();
    }

    public function init()
    {
        return R('App/Common/init');
    }

    public function oauthRegister($wxuser)
    {
        return R('App/Common/oauthRegister', array($wxuser));
    }

    /**
     * [index 这里是显示抢辣椒酱的界面].
     *
     * @return [type] [description]
     */
    public function index()
    {
        // 验证用户
        if (!session('sqopenid')) {
            $weObj = $this->init();
            $token = $weObj->getOauthAccessToken();
            if (!$token) {
                $weObj = $this->init();
                $url = $weObj->getOauthRedirect($this->appUrl.u_addons('Jam://App/Index/index'), '', 'snsapi_base');
                header("location: $url");

                return;
            } else {
                session('sqopenid', $token['openid']);
            }
        }
        $userDB = M('Vip');
        $user = $userDB->where(array('openid' => session('sqopenid')))->find();
        session('userId', $user['id']);
        session('user', $user);

        // 多少人可以参与subscribe= 1 AND 
        $sql = 'SELECT pid, COUNT(pid) AS num FROM wfx_vip WHERE pid NOT IN( SELECT vipid FROM wfx_jam_log WHERE iswin= 1) AND path != \'0\' GROUP BY pid';
        $person = $userDB->query($sql);
        $i = 0;
        foreach ($person as $key => $value) {
            if ($value['num'] >= $this->jam_config['neednum']) {
                ++$i;
            }
        }
        $this->assign('num', $i);

        // 获得辣椒酱的人，用于滚动
        $logDB = M('jam_log');
        $cond = array(
            'iswin' => 1,
        );
        $winners = $logDB->alias('winner')->join($userDB->getTableName().' as vip on winner.vipid=vip.id', 'left')
        ->field('nickname')->order('atime asc')->where($cond)->select();
        $this->assign('winners', $winners);

        // 剩余多少人，好友人数，我是否抢过了，我是否得过酱了
        $cond = array(
            'pid' => session('userId'),
            // 'subscribe'=>1
        );
        $fricount = $userDB->where($cond)->count();
        $myfriendcount = $this->jam_config['neednum'] - $fricount;
        $this->assign('left', $myfriendcount);
        $this->assign('fricount', $fricount);

        //本轮的中奖人，如果中奖人数已经等于配置文件的人数，则显示倒计时
        $first = strtotime($this->jam_config['astime'].' '.$this->jam_config['edstime']);
        $last = strtotime($this->jam_config['aetime'].' '.$this->jam_config['edetime']);

        // 当天时间
        $todate = date('Y-m-d');
        $todate1 = strtotime($todate.' '.$this->jam_config['edstime']);
        $todate2 = strtotime($todate.' '.$this->jam_config['edetime']);

        $timeNow = time();
        // 默认抢过了
        $everQiang = 1;

        $everGotCond = array(
            'vipid' => session('userId'),
            'iswin' => 1,
        );
        $everGot = $logDB->where($everGotCond)->getField('id') ? 1 : 0;
        // 不在活动日期
        if (!($timeNow >= $first && $timeNow <= $last)) {
            $nexttime = 9999999;
        } elseif (!($timeNow >= $todate1 && $timeNow <= $todate2)) {
            // 在活动日期内，但是不在活动时间内
            if ($timeNow < $todate1) {
                $nexttime = $todate1 - $timeNow;
            } elseif ($timeNow > $todate2) {
                $nexttime = 86400 - $timeNow % 86400 - 28800; //要加上8个小时...
            }
        } else {
            // 在活动时间内
            $nexttime = 3600 - $timeNow % 3600;
            // 当前时间段内是否抢过了？
            $everQiangCond = array(
                'atime' => array('between', array($timeNow + $nexttime - 3600, $timeNow + $nexttime)),
                'vipid' => session('userId'),
            );
            $everQiang = $logDB->where($everQiangCond)->getField('id');
        }
        if (($nexttime > 3600 || $timeNow < $todate1 || $timeNow > $todate2) || ($fricount < $this->jam_config['neednum']) || $everGot || $everQiang) {
            // 按钮设置成灰色
            $this->assign('btnColor', 'dark');
        } else {
            $this->assign('btnColor', 'light');
        }

        $this->assign('nexttime', $nexttime);
        $this->display();
    }

    public function getTime($second)
    {
        return floor($second / 3600).'小时'.floor($second % 3600 / 60).'分'.($second % 60).'秒';
    }

    /**
     * [qiang 点击按钮].
     *
     * @return [type] [description]
     */
    public function qiang()
    {
        if (!session('userId')) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '请从微信公众平台上进入'));
        }
        $timeNow = time();
        $first = strtotime($this->jam_config['astime'].' '.$this->jam_config['edstime']);
        $last = strtotime($this->jam_config['aetime'].' '.$this->jam_config['edetime']);

        $todate = date('Y-m-d');
        $todate1 = strtotime($todate.' '.$this->jam_config['edstime']);
        $todate2 = strtotime($todate.' '.$this->jam_config['edetime']);

        if (!($timeNow >= $first && $timeNow <= $last) || !($timeNow >= $todate1 && $timeNow <= $todate2)) {
            // 不在活动时间内
            $ret = array(
                'status' => 0,
                'msg' => '每天中午12点准时开启，每小时2瓶，快去邀请小伙伴！',
            );
            if ($timeNow > $last) {
                // 已结束
                $ret = array(
                    'status' => 0,
                    'msg' => '活动已结束',
                );
            }
            $this->ajaxReturn($ret);
        }

        // 是否已经抢过了
        $cond = array(
            'vipid' => session('userId'),
        );

        $logDB = M('jam_log');
        $log = $logDB->where($cond)->select();

        $time1 = $timeNow - $timeNow % 3600;
        $time2 = $timeNow - $timeNow % 3600 + 3600 - 1;

        $qianged = false;
        $won = false;
        foreach ($log as $key => $value) {
            if ($value['atime'] >= $time1 && $value['atime'] <= $time2) {
                $qianged = true;
            }
            if ($value['iswin'] == 1) {
                $won = true;
            }
        }
        // 是否已经中过奖
        if ($won) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '您已获得过一瓶辣椒酱（58元余额已到账），请直接在商城购买用余额支付即可。'));
        }
        // 已经抢过了
        if ($qianged) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '每人每轮只有一次机会'));
        }
        // 获取他的好友人数，必须够人数
        $vipCond = array(
            'pid' => session('userId'),
            // 'subscribe' => 1,
        );
        $fricount = M('vip')->where($vipCond)->count();
        if ($fricount < $this->jam_config['neednum']) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '邀请的人数不够，赶紧邀请更多的人吧'));
        }
        // 中奖人数不能大于配置文件
        $wonCond = array(
            'atime' => array('between', array($time1, $time2)),
            'iswin' => 1,
        );
        if ($logDB->where($wonCond)->count('id') >= $this->jam_config['eachhour']) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '被别人捷足先登了，等下一轮吧'));
        }

        
        

        // 给他抢
        $logData = array(
            'vipid' => session('userId'),
            'atime' => $timeNow,
            'iswin' => 1,
        );
        // $cardCond = array(
        //     'vipid' => array('exp', 'is null'),
        //     'status' => 0,
        // );
        // $vipCardDB = M('vip_card');
        // $vip_card = $vipCardDB->where($cardCond)->find();
        // $vip_card['vipid'] = session('userId');
        // $vip_card['status'] = 1; 
        $user = session('user');
        $user['money'] += 58;

        $data_log['ip'] = get_client_ip(0, true);    //源自微信注册
        $data_log['vipid'] = $user['id'];
        $data_log['ctime'] = time();
        $data_log['openid'] = $user['openid'];
        $data_log['nickname'] = $user['nickname'];
        $data_log['event'] = '抢到辣椒酱';
        $data_log['score'] = 0;
        $data_log['money'] = 58;
        $data_log['exp'] = 0;
        $data_log['type'] = 99;


        $logDB->startTrans();
        $res1 = $logDB->add($logData);
        $rlog = M('Vip_log')->add($data_log);
        $res3 = M('vip')->save($user);
        // $res2 = $vipCardDB->save($vip_card);

        if ($res1 && $res2 !== false && $res3 !== false) {
            $logDB->commit();
            $this->ajaxReturn(array('status' => 1, 'msg' => '恭喜获得一瓶辣椒酱（58元余额已到账），请直接在商城购买用余额支付即可。'));
        } else {
            $logDB->rollback();
            $this->ajaxReturn(array('status' => 0, 'msg' => '错误，请联系客服。'));
        }
    }

    /**
     * [getWinner 倒计时完成后].
     *
     * @return [type] [description]
     */
    public function getWinners()
    {
        $logDB = M('jam_log');
        $vip = M('vip');
        $cond = array(
            'iswin' => 1,
        );
        $winners = $logDB->alias('winner')->join($vip->getTableName().' as vip on winner.vipid=vip.id', 'left')
        ->field('nickname')->order('atime asc')->where($cond)->limit(I('get.n', 3, 'intval'))->select();
        $this->ajaxReturn($winners);
    }

    /**
     * [getMyFriends 获取我的朋友].
     *
     * @return [type] [description]
     */
    public function getMyFriends()
    {
        if (!session('userId')) {
            $this->ajaxReturn(array('status' => 0));
        }
        $cond = array(
            'pid' => session('userId'),
            // 'subscribe' => 1,
        );
        $friends = M('vip')->where($cond)->field("headimgurl, nickname, from_unixtime(subscribe_time, '%m-%d %H:%s') as st")->page(I('get.p'))->limit(10)->select();
        // dump(M('vip')->getLastSql());
        $this->ajaxReturn($friends);
    }
}
