<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        // printPP(1);
        $this->display();
    }

    public function help()
    {
        $this->display();
    }

    public function about()
    {
        $temp = M('Shop_set')->find();
        $this->assign('shop', $temp);
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
}
