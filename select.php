<?php 
set_time_limit(600);
//设定页面编码
header("Content-Type:text/html;charset=utf-8");
//设定时区
date_default_timezone_set('Asia/Shanghai');

error_reporting(E_ALL ^ E_NOTICE);

//禁止浏览器访问
if (PHP_SAPI != 'cli') {
    die();
}

//获取命令行参数
$argsData = array();
$argsData['url'] = $argv[1];
echo "#Notice :  Wait a minute .............." . PHP_EOL;
// die;

//全局加载
include_once(dirname(__FILE__).'/conf/include.php');
//配置变量加载
include_once(dirname(__FILE__).'/conf/incloudeURL.php');

if (isset($argsData['url'])) {
	$urlID = $argsData['url'];
	if (strstr($urlID,'://'))
    {
        $urlID = sha1($urlID);
    }
    $info = getBody($urlID);
	print_format($info,'news info');
}else{
    $dbo;
    $article = $dbo->loadAssoc("SELECT COUNT(1) as sucCount FROM `articles` WHERE `status` = 3");
    echo "\n>>>>>>>>>  总共成功采集提交文章: {$article['sucCount']}\n".PHP_EOL;
}







