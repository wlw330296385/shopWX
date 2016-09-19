<?php
define('IS_CGI',(0 === strpos(PHP_SAPI,'cgi') || false !== strpos(PHP_SAPI,'fcgi')) ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);
if (get_magic_quotes_gpc()) {
	$_GET = stripslashes_array($_GET);
	$_POST = stripslashes_array($_POST);
}
function stripslashes_array(&$array) {  
	while(list($key,$var) = each($array)) {  
		if ($key != 'argc' && $key != 'argv' && (strtoupper($key) != $key || ''.intval($key) == "$key")) {  
			if (is_string($var)) {  
				$array[$key] = stripslashes($var);  
			}  
			if (is_array($var))  {  
				$array[$key] = stripslashes_array($var);  
			}  
		}  
	}  
	return $array;  
}  
function lib_replace_end_tag($str)  
{  
	if (empty($str)) return false;  
	$str = htmlspecialchars($str);  
	$str = str_replace( '/', "", $str);  
	$str = str_replace("\\", "", $str);  
	$str = str_replace(">", "", $str);  
	$str = str_replace("<", "", $str);  
	$str = str_replace("<SCRIPT>", "", $str);  
	$str = str_replace("</SCRIPT>", "", $str);  
	$str = str_replace("<script>", "", $str);  
	$str = str_replace("</script>", "", $str);  
	$str=str_replace("select","select",$str);  
	$str=str_replace("join","join",$str);  
	$str=str_replace("union","union",$str);  
	$str=str_replace("where","where",$str);  
	$str=str_replace("insert","insert",$str);  
	$str=str_replace("delete","delete",$str);  
	$str=str_replace("update","update",$str);  
	$str=str_replace("like","like",$str);  
	$str=str_replace("drop","drop",$str);  
	$str=str_replace("create","create",$str);  
	$str=str_replace("modify","modify",$str);  
	$str=str_replace("rename","rename",$str);  
	$str=str_replace("alter","alter",$str);  
	$str=str_replace("cas","cast",$str);  
	$str=str_replace("&","&",$str);  
	$str=str_replace(">",">",$str);  
	$str=str_replace("<","<",$str);  
	$str=str_replace(" ",chr(32),$str);  
	$str=str_replace(" ",chr(9),$str);  
	$str=str_replace("    ",chr(9),$str);  
	$str=str_replace("&",chr(34),$str);  
	$str=str_replace("'",chr(39),$str);  
	$str=str_replace("<br />",chr(13),$str);  
	$str=str_replace("''","'",$str);  
	$str=str_replace("css","'",$str);  
	$str=str_replace("CSS","'",$str);  
	  
	return $str;  
  
}  
if(!IS_CLI) {
	// 当前文件名
	if(!defined('_PHP_FILE_')) {
		if(IS_CGI) {
			//CGI/FASTCGI模式下
			$_temp  = explode('.php',$_SERVER['PHP_SELF']);
			define('_PHP_FILE_',    rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
		}else {
			define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
		}
	}
	if(!defined('__ROOT__')) {
		$_root  =   rtrim(dirname(_PHP_FILE_),'/');
		define('__ROOT__',  (($_root=='/' || $_root=='\\')?'':$_root));
	}
}

$wOpt = $_GET;
$wOpt['package'] = 'prepay_id=' . lib_replace_end_tag($wOpt['package']);
//var_dump("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
?>
<script type="text/javascript">
document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
	WeixinJSBridge.invoke('getBrandWCPayRequest', {
		'appId' : '<?php echo lib_replace_end_tag($wOpt['appId']);?>',
		'timeStamp': '<?php echo lib_replace_end_tag($wOpt['timeStamp']);?>',
		'nonceStr' : '<?php echo lib_replace_end_tag($wOpt['nonceStr']);?>',
		'package' : '<?php echo lib_replace_end_tag($wOpt['package']);?>',
		'signType' : '<?php echo lib_replace_end_tag($wOpt['signType']);?>',
		'paySign' : '<?php echo lib_replace_end_tag($wOpt['paySign']);?>'
	}, function(res) {
		if(res.err_msg == 'get_brand_wcpay_request:ok') {
			//alert('您已成功付款！ ');
			window.location.href='http://<?php echo $_SERVER['HTTP_HOST'].__ROOT__;?>/App/Shop/orderList/sid/0/';
		} else {
			//alert('启动微信支付失败, 请检查你的支付参数. 详细错误为: ' + res.err_msg);
			window.location.href='http://<?php echo $_SERVER['HTTP_HOST'].__ROOT__;?>/App/Shop/orderList/sid/0/';
		}
	});
}, false);
</script>