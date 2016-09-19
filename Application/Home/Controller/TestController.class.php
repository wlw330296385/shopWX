<?php

namespace Home\Controller;

use Think\Controller;

class TestController extends Controller
{
    public function __construct(){
        header("Content-type:text/html;charset=utf-8");
    }
    public function play()
    {
        // $sql=array(
        //     'vipid'=>67,
        //     'money'=>29,
        //     'cardno'=>rand(1000000000,9999999999),
        //     'usemoney'=>29,
        //     'stime'=>time(),
        //     'etime'=>time()+50000,
        //     );
        // M('vip_card')->add($sql);
        // $vipid=I('post.vipid');
        // $openid=M('vip')->where(array('id'=>$vipid))->getField('openid');$cardNO=I('post.cardNO');
        if (IS_POST) {
            $viplist=M('vip_card')->select();
            $_set = M('Set')->find();
            $options['appid'] = $_set['wxappid'];
            $options['appsecret'] = $_set['wxappsecret'];
            $wx = new \Util\Wx\Wechat($options);
            foreach ($viplist as $key => $value) {
                $openid=M('vip')->where(array('id'=>$value['vipid']))->getField('openid');
                if ($openid) {
                $data = array();
                $data['touser'] = $openid;
                $data['template_id'] = 'cqM0Q7Qvr8paKX0fwTslsqIAaFCRKWAsj6otu17q9kc';
                $data['topcolor'] = '#00FF00';
                $data['data'] = array(
                    'first' => array('value' => '您的代金券已发放，详情如下'),
                    'keyword1' => array('value' => $value['cardno']),
                    'keyword2' => array('value' => $value['money'].'元'),
                    'keyword3' => array('value' => '小海'),
                    'keyword4' => array('value' => date('Y-m-d',$value['stime'])),
                    'remark' => array('value' => '有效期至'.date('Y-m-d',$value['etime']).'日，可直接再来一瓶，感谢你对小海的支持！'),
                );
                $re = $wx->sendTemplateMessage($data); 
                if (!$re) {
                    file_put_contents('card.txt',json_encode($wx).PHP_EOL.json_encode($re),FILE_APPEND);
                }
            }
            } 
        }
        
        echo '确定则发送全部卡券消息';
        $this->display();
    }

    public function sorts1(){
        // $m=M('shop_order');
        $m=M('vip_address');
        // $order=$m->Field('vipaddress,id')->select();
        $order=$m->Field('address,id')->select();
        // dump($order);die;
        foreach ($order as $key => $value) {
            // $list=explode('市', $value);
            // $str = $value;
            // $pos = mb_strpos($value['vipaddress'], '市', 0, 'utf-8');
            // $value['vipcity'] = mb_substr($value['vipaddress'], $pos-2, 2, 'utf-8');
            // $value['vipprovince'] = mb_substr($value['vipaddress'],0, 2, 'utf-8'); 
            $pos = mb_strpos($value['address'], '市', 0, 'utf-8');
            $value['city'] = mb_substr($value['address'], $pos-2, 2, 'utf-8');
            $value['province'] = mb_substr($value['address'],0, 2, 'utf-8');  
            $m->save($value);
        }
        // dump($order);
    }
    public function sorts2(){
        $m=M('shop_order');
        // $m=M('vip_address');
        $order=$m->Field('vipaddress,id')->select();
        // $order=$m->Field('address,id')->select();
        // dump($order);die;
        foreach ($order as $key => $value) {
            // $list=explode('市', $value);
            // $str = $value;
            $pos = mb_strpos($value['vipaddress'], '市', 0, 'utf-8');
            $value['vipcity'] = mb_substr($value['vipaddress'], $pos-2, 2, 'utf-8');
            $value['vipprovince'] = mb_substr($value['vipaddress'],0, 2, 'utf-8'); 
            // $pos = mb_strpos($value['address'], '市', 0, 'utf-8');
            // $value['city'] = mb_substr($value['address'], $pos-2, 2, 'utf-8');
            // $value['province'] = mb_substr($value['address'],0, 2, 'utf-8');  
            $m->save($value);
        }
        // dump($order);
    }
}
