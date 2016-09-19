<?php
// +----------------------------------------------------------------------
// | 用户后台基础类--CMS分组商城管理类
// +----------------------------------------------------------------------
namespace Admin\Controller;

class TestController extends BaseController
{

    public function _initialize()
    {
        //你可以在此覆盖父类方法
        parent::_initialize();
        //初始化两个配置
        self::$CMS['shopset'] = M('Shop_set')->find();
        self::$CMS['vipset'] = M('Vip_set')->find();
    }

    public function index(){
         $config = array(
            'mimes' => array(), //允许上传的文件MiMe类型
            'maxSize' => 0, //上传的文件大小限制 (0-不做限制)
            'exts' => array('jpg', 'gif', 'png', 'jpeg'), //允许上传的文件后缀
            'autoSub' => true, //自动子目录保存文件
            'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath' => './Uploads/', //保存根路径
            'savePath' => 'img/', //保存路径
            'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt' => '', //文件保存后缀，空则使用原后缀
            'replace' => false, //存在同名是否覆盖
            'hash' => true, //是否生成hash编码
            'callback' => false, //检测文件是否存在回调，如果存在返回文件信息数组
            'driver' => '', // 文件上传驱动
            'driverConfig' => array(), // 上传驱动配置
        );
        //var_dump($_FILES);
        $up = new \Util\Upload($config);
        dump($up);exit;
        if ($list = $up->upload($_FILES)) {
            $pic = M('Upload_img');
            $count = 0;
            $arr = array();
            foreach ($list as $k => $v) {
                //$arr['uid']=$uid;
                $arr['name'] = $list[$k]['name'];
                $arr['ext'] = $list[$k]['ext'];
                $arr['type'] = 'img';
                $arr['savename'] = $list[$k]['savename'];
                $arr['savepath'] = $list[$k]['savepath'];
                $re = $pic->add($arr);
                if ($re) {
                    $count += 1;
                }
            }

            if ($count) {
                $backstr = "'" . $count . "张图片上传成功！'" . ',' . "true";
                echo $backstr;
            } else {
                echo "$count为空";
            };

        } else {
            echo $up->getError();
        };
    	$this->display();
    }
}