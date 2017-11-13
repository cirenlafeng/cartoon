<?php

include_once(dirname(__FILE__).'/../path.php');

//加载配置
include_once(HOST_PATH.'/conf/curl.php');
include_once(HOST_PATH.'/conf/urlInfo.php');
include_once(HOST_PATH.'/conf/db.php');
// include_once(HOST_PATH.'/conf/url.php');//分配到control里面每个php里面写
// //类库
include_once(HOST_PATH.'/lib/php-curlmulti/src/Core.php');
include_once(HOST_PATH.'/lib/php-query/phpQuery/phpQuery.php');
include_once(HOST_PATH.'/lib/simple_html_dom/simple_html_dom.php');
include_once(HOST_PATH.'/lib/dbo.php');
// //方法
include_once(HOST_PATH.'/model/fun.php');
include_once(HOST_PATH.'/model/curlSet.php');
include_once(HOST_PATH.'/model/pushAPI.php');
include_once(HOST_PATH.'/model/articlesDB.php');
include_once(HOST_PATH.'/model/pushAPILogDB.php');
include_once(HOST_PATH.'/model/pushRepeatDB.php');
include_once(HOST_PATH.'/model/postResourceGather.php');

$statisticsInfo = array();


//初始化db
$dbo = dbo();