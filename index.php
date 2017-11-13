<?php 
set_time_limit(600);
//设定页面编码
header("Content-Type:text/html;charset=utf-8");
//设定时区
date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//全局加载
include_once(dirname(__FILE__).'/conf/include.php');
/*
*	采集链接：http://localhost/webEngine/index.php?c=arabic.cnn.com&u=0&a=url
*	采集内容：http://localhost/webEngine/index.php?c=arabic.cnn.com&a=body&u=7a863d2bc014d00fa678294f6070a8ec
*	提交内容：http://localhost/webEngine/index.php?c=arabic.cnn.com&a=post&u=0d294700b6dedf6bf4c2274d77ff5682a7c2d0b8
*/

if (!isset($_GET['c'])) {
    exit("need controler[c]\n");
}
if (!isset($_GET['u'])) {
    exit("need url index[u]\n");
}
if (!isset($_GET['a'])) {
    exit("need action[a]\n");
}
//载入业务
include_once(getIncludeFile($_GET['c']));
//配置变量加载
include_once(dirname(__FILE__).'/conf/incloudeURL.php');
//方法名前缀
$callFunName = getControlFunFirstName($_GET['c']).'_Funtion';
switch ($_GET['a'])
{
    case 'url'://_GetUrlList
        $curlFun = 'urlWork';
        break;
    case 'body'://_GetBodyInfo
        $curlFun = 'bodyWork';
        break;
    case 'post':
    {
        postDataForAPIByUrlID($_GET['u']);
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
    $control = $_GET['c'];
    $index = $_GET['u'];
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
    //特殊设置
    $curl = setCurlOPT($control, $curl);
    $curl->add([
        'url' => $urlData['url'],
        'args' => $urlData,
    ], $callFunName);
    $curl->cbTask = null;
}
function bodyWork()
{
    global $curl;
    global $callFunName;
    //找数据
    $control = $_GET['c'];
    $urlID = $_GET['u'];
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
        //抓取页面html内容
        if (strlen($result['url']) > 5) {
            $curl->add([
                'url' => $result['url'],
                'args' => $result,
            ], $callFunName);
            $curl->cbTask = null;
        }
    }
}
// print_format($reData, $_GET['action'].' result');
