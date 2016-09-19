<?php
// 本类由系统自动生成，仅供测试用途
namespace Admin\Controller;

class UploadController extends BaseController
{
    public function index()
    {
        $this->display();
    }
    public function uploadOSS()
    {
        //配置config
        $bucket=C('ALIOSS_CONFIG.BUCKET');
        $config=C('ALIOSS_CONFIG');
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     '/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录

        //获取文件路径
        if (!$_FILES) {
            echo "<script>parent.doupimgcallback('图片不存在',false)</script>";
            die();
        }
        // var_dump($_FILES);
        $houzhui=explode('.',$_FILES['upfile']['name']);
        $time=time();
        $object='shop/'.$time.'.'.$houzhui[1];
       
        $path=$_FILES['upfile']['tmp_name'];
        //实例化OSS
        $oss=new \OSS\OssClient($config['KEY_ID'],$config['KEY_SECRET'],$config['END_POINT']);
        //上传
        $re=$oss->uploadFile($bucket,$object,$path);
        if (!$re) {
           echo "<script>parent.doupimgcallback('上传到阿里云出错了',false)</script>";
        }
        if ($re) {
            $url=$re->{'header'}['_info']['url'];
            $url='http://img1.nntzd.com/shop/'.$time.'.'.$houzhui[1];
            $arr=array(
                'name'=>$_FILES['upfile']['name'],
                'ext'=>$houzhui[1],
                'type'=>$houzhui[1],
                'savename'=>$time.'.'.$houzhui[1],
                'savepath'=>$url
                );
            // var_dump($arr);die;
            $id=M('Upload_img')->add($arr);
            if ($id) {
               echo  "{'url':'" .$arr['savename']. "','title':'" .$arr['name']. "','original':'" .$arr['name']. "','state':'SUCCESS'}";
            }            
         else {
            echo "<script>parent.doupimgcallback('图片保存时失败！',false)</script>";
            }
        } 
    }
    public function indeximg()
    {
        //查找带回字段
        $fbid = I('fbid');
        $isall = I('isall');
        $this->assign('fbid', $fbid);
        $this->assign('isall', $isall);
        $page = '1,8';
        $m = M('Upload_img');
        $cache = $m->page($page)->order('id desc')->select();
        $this->assign('cache', $cache);
        $this->ajaxReturn($this->fetch());
    }

    // public function doupimg()
    // {

    //     $config = array(
    //         'mimes' => array(), //允许上传的文件MiMe类型
    //         'maxSize' => 0, //上传的文件大小限制 (0-不做限制)
    //         'exts' => array('jpg', 'gif', 'png', 'jpeg'), //允许上传的文件后缀
    //         'autoSub' => true, //自动子目录保存文件
    //         'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
    //         // 'rootPath' => './Uploads/', //保存根路径
    //         'savePath' => 'img/', //保存路径
    //         'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
    //         'saveExt' => '', //文件保存后缀，空则使用原后缀
    //         'replace' => false, //存在同名是否覆盖
    //         'hash' => true, //是否生成hash编码
    //         'callback' => false, //检测文件是否存在回调，如果存在返回文件信息数组
    //         'driver' => '', // 文件上传驱动
    //         'driverConfig' => array(), // 上传驱动配置
    //     );
    //     //var_dump($_FILES);
    //     // $up = new \Util\Upload($config);
    //     // file_put_contents('./Data/app_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.json_encode($_FILES).PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL,FILE_APPEND);
    //     $list['savepath']=$this->upOss();
    //     // 上传文件 
    //     // if ($list = $up->upload($_FILES)) {
    //         // $pic = M('Upload_img');
    //         // $count = 0;
    //         // $arr = array();
    //         // foreach ($list as $k => $v) {
    //         //     //$arr['uid']=$uid;
    //         //     $arr['name'] = $list[$k]['name'];
    //         //     $arr['ext'] = $list[$k]['ext'];
    //         //     $arr['type'] = 'img';
    //         //     $arr['savename'] = $list[$k]['savename'];
    //         //     $arr['savepath'] =$list[$k]['savepath'];
    //         //     $re = $pic->add($arr);
    //         //     if ($re) {
    //         //         $count += 1;
    //         //     }
    //         // }

    //         if ($list['savepath']) {
    //             $re = M('Upload_img')->add($arr);
    //             $backstr = "'" . $count . "张图片上传成功！'" . ',' . "true";
    //             echo "<script>parent.doupimgcallback(" . $backstr . ")</script>";
    //         } else {
    //             echo "<script>parent.doupimgcallback('图片保存时失败！',false)</script>";
    //         }

    //     // } else {
    //     //     echo "<script>parent.doupimgcallback('" . $up->getError() . "',false)</script>";
    //     // };

    // }
    public function doupimg(){
        // 获取配置项
        $bucket=C('ALIOSS_CONFIG.BUCKET');
        $config=C('ALIOSS_CONFIG');
        //获取文件路径
        if (!$_FILES) {
            echo "<script>parent.doupimgcallback('图片不存在',false)</script>";
            die();
        }
        $houzhui=explode('/',$_FILES['appfile']['type'][0]);
        $time=time();
        $object='shop/'.$time.'.'.$houzhui[1];
       
        $path=$_FILES['appfile']['tmp_name'][0];
        //实例化OSS
        $oss=new \OSS\OssClient($config['KEY_ID'],$config['KEY_SECRET'],$config['END_POINT']);
        //上传
        $re=$oss->uploadFile($bucket,$object,$path);
        if (!$re) {
           echo "<script>parent.doupimgcallback('上传到阿里云出错了',false)</script>";
        }
        if ($re) {
            $url=$re->{'header'}['_info']['url'];
            $url='http://img1.nntzd.com/shop/'.$time.'.'.$houzhui[1];
            $arr=array(
                'name'=>$_FILES['appfile']['name'][0],
                'ext'=>$houzhui[1],
                'type'=>$houzhui[0],
                'savename'=>$time.'.'.$houzhui[1],
                'savepath'=>$url
                );
            $id=M('Upload_img')->add($arr);
            if ($id) {
               echo "<script>parent.doupimgcallback('图片上传成功',true)</script>";
            }            
         else {
            echo "<script>parent.doupimgcallback('图片保存时失败！',false)</script>";
            }
        }        
    }
    public function delimgs()
    {
        if (IS_AJAX) {
            $m = M('Upload_img');
            $list = $m->delete(I(ids));
            if ($list == true) {
                $data['status'] = 1;
                $data['msg'] = '成功删除' . $list . '张图片！';
            } else {
                $data['status'] = 0;
                $data['msg'] = '删除失败，请重试或联系管理员！';
            }
            $this->ajaxReturn($data, 'JSON');
        } else {
            $this->error('微专家提醒您：禁止外部访问！');
        }
    }


    public function getmoreimg()
    {
        $page = I('p') . ',8';
        $m = M('Upload_img');
        $cache = $m->page($page)->order('id desc')->select();
        if ($cache) {
            $this->assign('cache', $cache);
            $this->ajaxReturn($this->fetch());//封装模板fetch并返回
        } else {
            $this->ajaxReturn("");
        }

    }

/**
 * 实例化阿里云oos
 * @return object 实例化得到的对象
 */
function new_oss(){
    vendor('Alioss.autoload');
    $config=C('ALIOSS_CONFIG');
    $oss=new \OSS\OssClient($config['KEY_ID'],$config['KEY_SECRET'],$config['END_POINT']);
    return $oss;
}
 
/**
 * 上传文件到oss并删除本地文件
 * @param  string $path 文件路径
 * @return bollear      是否上传
 */
function oss_upload($path){

    // 获取配置项
    $bucket=C('ALIOSS_CONFIG.BUCKET');
    // 先统一去除左侧的.或者/ 再添加./
    // $oss_path=ltrim($path,'./');
    $object = 'test/'.str_replace('\\','/',$path);

    $object = str_replace(':','huiluyou',$object);

    if (file_exists($path)) {

        // 实例化oss类
        $oss=new_oss();
        // 上传到oss 

        return $oss->uploadFile($bucket,$object,$path);

        // 如需上传到oss后 自动删除本地的文件 则删除下面的注释 
        

        return true;
    }
}
}