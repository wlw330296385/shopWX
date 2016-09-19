<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Addons\Jam\Controller;

class AdminController extends InitController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $config = M('jam_config')->find();
        $this->assign('config', $config);

        // $user = M('jam_user');
        // $vips = $user->alias('user')->join(M('vip')->getTableName().' as vip on user.vipid=vip.id', 'left')->order('fnum desc')->field('vip.id, nickname')->select();
        // $this->assign('record', $vips);
        $this->display();
    }

    public function addConfig()
    {
        $strarr = explode(' - ', I('post.acdate'));
        $statime = explode(' - ', I('post.actime'));
        $arr = array(
            'astime' => $strarr[0],
            'aetime' => $strarr[1],
            'edstime' => $statime[0],
            'edetime' => $statime[1],
            'eachhour' => I('post.eachhour'),
            'neednum' => I('post.neednum'),
            'id' => I('post.id'),
            'actived' => I('post.actived'),
        );
        if (M('jam_config')->save($arr) !== false) {
            file_put_contents('data.txt', M('jam_config')->getLastSql());
            $this->ajaxReturn(array('status' => 1, 'msg' => '提交成功'));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '提交失败'));
        }
    }
}
