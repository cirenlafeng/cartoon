<?php 
set_time_limit(600);
//设定页面编码
header("Content-Type:text/html;charset=utf-8");
//设定时区
date_default_timezone_set('Asia/Shanghai');

error_reporting(E_ALL ^ E_NOTICE);

//禁止浏览器访问
if (PHP_SAPI != 'cli') {
    return phpinfo();
}

//获取命令行参数
if (count($argv) != 4) {
	var_dump($argv);
	echo "need argc :   controler      action       url [urlArray.index or urlID]" . PHP_EOL;die();
}
$argsData = array();
$argsData['c'] = $argv[1];
$argsData['a'] = $argv[2];
$argsData['u'] = $argv[3];
var_dump($argsData);
echo "#Notice :  Wait a minute .............." . PHP_EOL;
// die;

//全局加载
include_once(dirname(__FILE__).'/conf/include.php');

/*
*	采集链接：php command.php arabic.cnn.com url 0
*	采集内容：php command.php arabic.cnn.com body 0d294700b6dedf6bf4c2274d77ff5682a7c2d0b8
*	提交内容：php command.php arabic.cnn.com post 0d294700b6dedf6bf4c2274d77ff5682a7c2d0b8
*/


if (!isset($argsData['c'])) {
	exit("need controler[c]\n");
}
if (!isset($argsData['a'])) {
	exit("need action[a]\n");
}
if (!isset($argsData['u'])) {
	exit("need url index[u]\n");
}

//载入业务
include_once(getIncludeFile($argsData['c']));

//配置变量加载
include_once(dirname(__FILE__).'/conf/incloudeURL.php');

//方法名前缀
$callFunName = getControlFunFirstName($argsData['c']).'_Funtion';
switch ($argsData['a'])
{
	case 'url'://_GetUrlList
		$curlFun = 'urlWork';
		break;
	case 'body'://_GetBodyInfo
		$curlFun = 'bodyWork';
		break;
	case 'post':
	{
		postDataForAPIByUrlID($argsData['u']);
		return;
	}
		break;
	default:
		exit('action is error !');
		break;
}

## Fetch start
use Ares333\CurlMulti\Core;
$stime = microtime(true);
$curl = new Core();
$curl->opt [CURLOPT_USERAGENT]	 	= getUserAgentInfo();
$curl->opt [CURLOPT_HTTPHEADER]	 	= getUserIP();
$curl->opt [CURLOPT_REFERER]	 	= getUserReferer();
$curl->opt [CURLOPT_SSL_VERIFYPEER]	= FALSE;//不验证SSL
$curl->opt [CURLOPT_SSL_VERIFYHOST]	= FALSE;//不验证SSL
// $curl->opt [CURLOPT_ENCODING]		= 'gzip';
$curl->cbTask = array($curlFun);
$curl->maxThread = 5;//线程数
$curl->maxTry = 2;//失败重试
$curl->start();
$etime = microtime(true);
echo "Finished in .. ". round($etime - $stime, 3) ." seconds\n";

function urlWork()
{
	global $curl;
	global $callFunName;
	global $urlInfo;
	global $argsData;

	$control = $argsData['c'];
	$index = $argsData['u'];
    if (!isset($urlInfo[$control][$index]))
    {
        echo "#Error : urlInfo[{$control}][{$index}] is none !...... ".PHP_EOL;die();
    }
	$urlData = $urlInfo[$control][$index];
	$urlData['domain'] = $control;
	$urlData['fun'] = 'getUrl';
    if ($urlData['weight'] == 99)
    {
        echo "#Error :  weight == 99 |  {$urlData['url']}".PHP_EOL;die();
    }

	echo "#Finding :  =======>>>>>>  {$urlData['url']}".PHP_EOL;

	//特殊设置
	$curl = setCurlOPT($control, $curl);
	
	$curl->add([
	    'url' => $urlData['url'],
	    'args' => $urlData,
	], $callFunName);

	$curl->cbTask = null;
	// print_format($urlData);
}

function bodyWork()
{
	global $curl;
	global $callFunName;
	global $argsData;

	//找数据
	$control = $argsData['c'];
	$urlID = $argsData['u'];
	$result = getBody($urlID);
	$result['fun'] = 'getBody';

	//特殊设置
	$curl = setCurlOPT($control, $curl);
	
	if (strlen($result['html']) > 1000)
	{
		//已存在html内容
		$arg1['info']['http_code'] = 200;
		$arg1['content'] = $result['html'];
		$callFunName($arg1, $result);
	}else{
		echo "#Finding : {$result['url']}".PHP_EOL;
		//抓取页面html内容
		if (strlen($result['url']) > 5) {
			$curl->add([
			    'url' => $result['url'],
			    'args' => $result,
			], $callFunName);
		}
	}
	$curl->cbTask = null;
}





// print_format($reData, $_GET['action'].' result');






