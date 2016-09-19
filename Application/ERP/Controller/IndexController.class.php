<?php
namespace ERP\Controller;
use Think\Controller;
class IndexController extends Controller
{
	public static $table;
    protected function _initialize()
    {
        header("content-type:text/html;charset=utf-8");
        header( 'Access-Control-Allow-Origin:*');
    }
	public function index()
    {
        $this->display();
    }
    public function GetGoodsInfo(){
    	$data['cate']=M('shop_storage_cate')->select();
    	$cid=I('get.cid');
    	$num=I('get.num');
    	$gmDB=M('shop_storage');
    	if ($cid) {
    		$data['goods']=$gmDB->where(array('cid'=>$cid))->select();
    	}
    	if ($num) {
            $gid=I('get.gid');
            $goodsnum=$gmDB->where(array('id'=>$gid))->getField('num');
    		if ($num>$data['goods']['num']) {
                $data['info']="库存不足";
                $data['status']=0;
            }
    	}
    	die(json_encode($data));
    }

    public function storage(){
        $data=I('post.data');
        $data['ctime']=time();
        if ($data['action']==0) {
            $res=M('shop_storage')->where(array('id'=>$data['gid']))->setDec('num',$data['num']);
        }else{
            $res=M('shop_storage')->where(array('id'=>$data['gid']))->setInc('num',$data['num']);
        }
        if ($res) {
            $re=M('shop_storage_log')->add($data);
                if ($re) { 
                    $msg['status']=1;
                }else{
                    $msg['status']=0;
                }
        }else{
            $msg['status']=2;
        }
        
        die(json_encode($msg));
    }
    
}