<?php
defined('HOST_PATH') or exit("path error");

$sqlBaseData = array(
    'list_id'=>0,
    'domain' =>0,
    'chapter_name'=>0,
    'chapter'=>0,
    'page'=>0,
    'pagecount' =>0,
    'pic'=>'',
    'thumbnail'=>'',
    'width' =>0,
    'height' =>0,
    'status'=>0,
    'create_time'=>time(),
    'update_time'=>time(),
);

function saveUrl($sql=array())
{
    global $statisticsInfo;

    if (!isset($sql['thumbnail']) || strlen($sql['thumbnail']) < 10) {
        echo "Error : articlesDB.saveUrl  -->>  saveUrlList sql[0] is NULL".PHP_EOL;return false;
    }

    // echo "#Found url : {$sql['url']}  ".PHP_EOL;return true;

    global $sqlBaseData;
    $sqlData = $sqlBaseData;

    foreach ($sql as $key => $value) {
        $sqlData[$key] = $value;
    }
    extract($sqlData);//$sqlData to 变量
    global $dbo;
    $exist = $dbo->loadObject("SELECT 1 FROM `comics_chapters` WHERE `list_id` = '{$list_id}' AND `chapter` = '{$chapter}' AND `page` = '{$page}' LIMIT 1");
    if ($exist) {
        echo "#Warning : articlesDB.saveUrl  -->>  list_id = {$list_id} , chapter = {$chapter} , page = {$page} is existed ....<br/>" . PHP_EOL;
        @$statisticsInfo['saveUrl']['#Warning']++;
        return false;
    }

    $sql = "INSERT INTO `comics_chapters` (`list_id`,`domain`, `chapter_name`, `chapter`, `status`, `page`, `pagecount`, `thumbnail`,`create_time`,`update_time` ) VALUES ('{$list_id}','{$domain}', '{$chapter_name}', '{$chapter}', '{$status}', '{$page}', '{$pagecount}','{$thumbnail}', '{$create_time}', '{$update_time}')";

    try {
        $dbo->exec($sql);
        echo "#Success : articlesDB.saveUrl  -->>  list_id = {$list_id} , chapter = {$chapter} , page = {$page} ....<br/>" . PHP_EOL;
        @$statisticsInfo['saveUrl']['#Success']++;
        return true;
    } catch (Exception $e) {
        echo "#Error : articlesDB.saveUrl  -->>  list_id = {$list_id} , chapter = {$chapter} , page = {$page} ....<br/>" . PHP_EOL;
        @$statisticsInfo['saveUrl']['#Error'][] = $domain.' '.$urlID;
        echo "---->>> #{$sql} <br/>" . PHP_EOL;
        return false;
    }
}

function saveUrlList($sqls, $urlData)
{
    $result = array('suc'=>0,'fal'=>0);
    if (@!is_array($sqls[0])) {
        global $statisticsInfo;
        $statisticsInfo['Error']['noUrlList'][$urlData['domain']][] = $urlData['url'];
        echo "Error : articlesDB.saveUrlList  -->>  sql[0] is NULL , listURL = {$urlData['url']}".PHP_EOL;return $result;
    }
    foreach ($sqls as $key => $sql) {
        if (saveUrl($sql)) {
            $result['suc']++;
        }else{
            $result['fal']++;
        }
    }
    return $result;
}

function saveHtml($sqlData='')
{
    global $dbo;
    global $statisticsInfo;
    // $sqlData to 变量
    extract($sqlData);
    // base64_encode[转义] base64_decode[反转义]
    $html = base64_encode($html);
    $sql = "UPDATE `articles` SET `status` = 1, `html` = '{$html}' WHERE `urlID` = '{$urlID}'";

    try{
        $dbo->exec($sql);
        echo "#Success : articlesDB.saveHtml  -->>  {$urlID} , domain = {$domain}  HTML resource update ..<br/>".PHP_EOL;
        @$statisticsInfo['saveHtml']['#Success']++;
    }catch (Exception $e)
    {
        echo "#Error : articlesDB.saveHtml  -->>  {$urlID} , url = {$url}  HTML resource can not update ..<br/>".PHP_EOL;
//        echo "Error SQL : #".$sql.PHP_EOL;
        echo "Exception ： ".$e->getMessage().PHP_EOL;
        @$statisticsInfo['saveHtml']['#Error_count']++;
        @$statisticsInfo['saveHtml']['#Error']['url'][] = $urlID.' -> '.$url;
    }
    return true;
}

function getBody($urlID='')
{
    global $dbo;
    $article = $dbo->loadAssoc("SELECT * FROM `articles` WHERE `urlID` = '{$urlID}' LIMIT 1");
    if (!$article) {
        echo "#Error : articlesDB.getBody  -->>  urlID[{$urlID}] ({$url}) is not found ....<br/>" . PHP_EOL;
        return false;
    }
    //处理html和content
    if (strlen($article['html']) > 10) {
        $article['html'] = base64_decode($article['html']);
    }
    if (strlen($article['content']) > 10) {
        $article['content'] = base64_decode($article['content']);
    }
    return $article;
}

function saveBody($sqlData='', $statuss = 2)
{
    global $dbo;
    global $statisticsInfo;
    // $sqlData to 变量
    if(empty($sqlData))
    {
        echo "#continue : articlesDB.saveBody  -->>  No Data , please try angin".PHP_EOL;
        return false;
    }
    extract($sqlData);
    // 过滤空格
    $time = trim($time);
    // base64_encode[转义] base64_decode[反转义]
    $content = base64_encode($content);

    if (strlen($pic) < 1) {
        echo "#Error : articlesDB.saveBody  -->>ID = {$list_id} ,chapter = {$chapter} page = {$page}  pic is null ..<br/>".PHP_EOL;
        @$statisticsInfo['saveBody']['#Error']['noTitle'][] = $urlID.' -> '.$url;
        return false;
    }
    if($status == 0){
        $sql = "UPDATE `comics_chapters` SET `status` = 0, `update_time` = '{$time}', `pic` = '',`width` = '{$width}',`height` = '{$height}' WHERE `id` = '{$id}'";
    }else{
        $sql = "UPDATE `comics_chapters` SET `status` = 2, `update_time` = '{$time}', `pic` = '{$pic}',`width` = '{$width}',`height` = '{$height}' WHERE `id` = '{$id}'";
    }
    
   
    
    // print_format($sql,'sql');return;
    // update
    try {
        $dbo->exec($sql);
        echo "#Success : articlesDB.saveBody  -->>  urlID = {$urlID} , domain = {$domain}  update ..<br/>".PHP_EOL;
        @$statisticsInfo['saveBody']['Success']++;
    } catch (Exception $e) {
        echo "#Error : articlesDB.saveBody  -->>  urlID = {$urlID} , domain = {$domain}  sql error ..<br/>".PHP_EOL;
        echo "Exception ： ".$e->getMessage().PHP_EOL;
        @$statisticsInfo['saveBody']['#Error_count']++;
        echo "#SQL : {$sql}".PHP_EOL;
    }

    return false;
}

function updateArticleStatus($urlID = '', $status = 3)
{
    global $dbo;
    $sql = "UPDATE `articles` SET `status` = {$status} WHERE `urlID` = '{$urlID}'";
    try {
        if(!$dbo->exec($sql)){
            echo "#Error : articlesDB.updateArticleStatus  -->>  urlID = {$urlID} , status = {$status} ..<br/>".PHP_EOL;
        }
    } catch (Exception $e) {
        echo "Exception ： ".$e->getMessage().PHP_EOL;
        echo "#SQL : $sql".PHP_EOL;
    }
//    if(!$dbo->exec("UPDATE `articles` SET `status` = {$status} WHERE `urlID` = '{$urlID}'")){
//        echo "#Error : articlesDB.updateArticleStatus  -->>  urlID = {$urlID} , status = {$status} ..<br/>".PHP_EOL;
//    }
}

function updateArticleHttpErrorCode($urlID = '', $httpCode = '200')
{
    global $dbo;
    $sql = "UPDATE `articles` SET `title` = '{$httpCode}' WHERE `urlID` = '{$urlID}'";
    try {
        if(!$dbo->exec($sql)){
            echo "#Error : articlesDB.updateArticleStatus  -->>  urlID = {$urlID} , title = {$httpCode} ..<br/>".PHP_EOL;
        }
    } catch (Exception $e) {
        echo "Exception ： ".$e->getMessage().PHP_EOL;
        echo "#SQL : $sql".PHP_EOL;
    }
}
