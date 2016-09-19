<?php
// 本类由系统自动生成，仅供测试用途
namespace App\Controller;

class UploadController extends BaseController
{
    
    public function uploadOSS()
    {
        //配置config
        $bucket=C('ALIOSS_CONFIG.BUCKET');
        $config=C('ALIOSS_CONFIG');
        //获取文件路径
        if (!$_FILES) {
            echo "<script>parent.doupimgcallback('图片不存在',false)</script>";
            die();
        }
        // var_dump($_FILES);die;
        $houzhui=explode('.',$_FILES['upfile']['name']);
        $time=time();
        $object='shop/'.$time.'.'.$houzhui[1];
       
        $path=$_FILES['upfile']['tmp_name'];
        //实例化OSS
        $oss=new \OSS\OssClient($config['KEY_ID'],$config['KEY_SECRET'],$config['END_POINT']);
        //上传
        $re=$oss->uploadFile($bucket,$object,$path);
        $msg['status']=0;//未知错误;
        if (!$re) {
           $msg['status']=2;//上传阿里云失败;
           die(json_encode($msg));
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
               // $msg['status']=1;
               // $msg['revpic']=$id;
               // $msg['savename']=$arr['savename'];
               echo '<img src="'.$url.'"  class="preview"  data-picid="'.$id.'">';
            }            
         else {
            $msg['status']=3;//保存数据库失败;
            }
        } 

        // die(json_encode($msg));
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

    $object = str_replace(':','shop/',$object);

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