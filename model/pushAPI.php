<?php
defined('HOST_PATH') or exit("path error");

function postDataForAPI($sqlData)
{

    global $urlInfo;
    if ($urlInfo[$sqlData['domain']][0]['isDone'] != 1)
    {
        // 后端未注册用户的采集列表
        echo "#Warning : post is stop . {$sqlData['domain']} 's isDone == {$urlInfo[$sqlData['domain']][0]['isDone']}".PHP_EOL;
        return false;
    }
    //排除不需要的
    switch (@$sqlData['status'])
    {
        case 0:
        case 1:
            echo "#Error : pushAPI.postDataForAPI  -->>  articles.status = [{$sqlData['status']}] need stop [need get html or bodyInfo] . urlID[{$sqlData['urlID']}]<br/>" . PHP_EOL;
            return false;
            break;
        case 3:
        case 7:
        case 10:
            echo "#Notice : pushAPI.postDataForAPI  -->>  articles.status = [{$sqlData['status']}] need stop [ posted ]. urlID[{$sqlData['urlID']}]<br/>" . PHP_EOL;
            return false;
            break;
        default:
            # 继续...
            break;
    }

    switch ($sqlData['type'])
    {
        case 1:
            postDataTypeBy_1($sqlData);
            break;
        case 2:
            postDataTypeBy_2($sqlData);
            break;
        case 3:
            postDataTypeBy_3($sqlData);
            break;
        default:
            break;
    }
}

function postDataTypeBy_1($sqlData)
{
    global $statisticsInfo;

    //处理API提交数据
    $postData = array('title'=>'', 'time'=>'', 'content'=>'', 'type'=>0, 'url'=>'', 'tag'=>0, 'check'=>1, 'keywords'=>'');
    foreach ($postData as $key => $value)
    {
        $postData[$key] = $sqlData[$key];
    }

    //模拟点赞数据
    $postData['like'] = rand(80,120);

    // unset($sqlData['html']);
    // print_format($sqlData);
    // print_format($postData);die();

    //处理入库数据
    $urlID = $sqlData['urlID'];
    $logData['urlID'] = $urlID;
    $logData['type'] = $postData['type'];
    $logData['status'] = 0;
    $logData['url'] = $postData['url'];
    $logData['pushInfo'] = $postData;
    $logData['operatorID'] = $sqlData['operatorID'];
    $logData['date'] = date('Y-m-d');

    // print_format($logData);die();

    //数据合法验证
    if (!isset($resultData['code'])) {
        $resultData = checkPostData($postData, $urlID);
    }

    //排重验证
    if (!isset($resultData['code'])) {
        if (checkRepeat($sqlData)) {
            echo "#Warning : pushAPI.postDataForAPI  -->>   urlID[{$urlID}] is repeated ....<br/>" . PHP_EOL;
            $resultData = array('code' => -10, 'message' => '重复数据');
        }
    }

    //提交数据
    if(!isset($resultData['code'])) {
        $resultData = postDataToAPI($postData,$urlID);
    }

    if (!isset($resultData['code'])) {
        echo "#Error! pushAPI.postDataForAPI  -->>   result ( NUll ) !  urlID = {$logData['urlID']}  post API is failure , resultMessage => # NULL # ..<br/>".PHP_EOL;
        // echo json_encode($postData).PHP_EOL;
        return false;
    }

    //更新数据
    $logData['resultCode'] = $resultData['code'];
    $logData['resultMsg'] = $resultData['message'];

    // var_dump($resultData);

    //处理
    $articleStatus = $sqlData['status'];
    switch ($resultData['code'])
    {
        case 1:
            $logData['status'] = 1;
            // echo "#Success! pushAPI.postDataForAPI  -->>   urlID = {$logData['urlID']}  post API is success , resultMessage => #".$resultData['message']."# ..<br/>".PHP_EOL;
            $articleStatus = 3;
            $statisticsInfo['pushAPI']['#Success']++;
            //记录发送状态
//            saveRepeatInfo($sqlData);
            break;
        case -10://重复数据
            $articleStatus = 10;
            $statisticsInfo['pushAPI']['#Repeat']++;
            break;
        default:
            if ($articleStatus < 4) {
                $articleStatus = 4;
            }else{
                if ($articleStatus < 7) {
                    $articleStatus++;
                }
            }
            echo "#Error! pushAPI.postDataForAPI  -->>   error code [{$logData['resultCode']}]!  urlID = {$logData['urlID']}  post API is failure , resultMessage => #".$resultData['message']."# ..<br/>".PHP_EOL;
            $statisticsInfo['pushAPI']['#Error']++;
            break;
    }

    //更新文章状态
    updateArticleStatus($urlID, $articleStatus);
    //保存发送记录
//    savePushAPILog($logData);
}

function postDataTypeBy_2($sqlData)
{
    // echo 'don\'t post type = 2 by now , domain = '.$sqlData['domain'].PHP_EOL;return;
    global $statisticsInfo;

    //处理API提交数据
    $videoInfo = unserialize($sqlData['content']);
    $postData = [
        'tag' => $sqlData['tag'],
        'type' => $sqlData['type'],
        'check' => $sqlData['check'],
        'videoTime' => $videoInfo['videoTime'],
        'description' => $videoInfo['description'],
        'videoID' => $videoInfo['videoID'],
        'url' => $sqlData['url'],
        'title' => $sqlData['title'],
        'status' => $sqlData['check'],
        'playTimes' => $videoInfo['playTimes'],
        'thumbnail' => $sqlData['thumbnail'],
        'like' => 0,
        'content' => ' ',
        'keywords' => $sqlData['keywords'],
    ];

    //模拟点赞数据
//    $postData['like'] = rand(80,120);
    $postData['like'] = 0;

        // unset($sqlData['html']);
    // print_format($sqlData);
    // print_format($postData);die();

    //处理入库数据
    $urlID = $sqlData['urlID'];
    $logData['urlID'] = $urlID;
    $logData['type'] = $postData['type'];
    $logData['status'] = 0;
    $logData['url'] = $postData['url'];
    $logData['pushInfo'] = $postData;
    $logData['operatorID'] = $sqlData['operatorID'];
    $logData['date'] = date('Y-m-d');

    // print_format($logData);die();

    //数据合法验证
    if (!isset($resultData['code'])) {
        $resultData = checkPostData($postData, $urlID);
    }

    //排重验证
    if (!isset($resultData['code'])) {
        if (checkRepeat($sqlData)) {
            echo "#Warning : pushAPI.postDataForAPI  -->>   urlID[{$urlID}] is repeated ....<br/>" . PHP_EOL;
            $resultData = array('code' => -10, 'message' => '重复数据');
        }
    }

    //提交数据
    if(!isset($resultData['code'])) {
        $resultData = postDataToAPI($postData,$urlID);
    }

    if (!isset($resultData['code'])) {
        echo "#Error! pushAPI.postDataForAPI  -->>   result ( NUll ) !  urlID = {$logData['urlID']}  post API is failure , resultMessage => # NULL # ..<br/>".PHP_EOL;
        // echo json_encode($postData).PHP_EOL;
        return false;
    }

    //更新数据
    $logData['resultCode'] = $resultData['code'];
    $logData['resultMsg'] = $resultData['message'];

    // var_dump($resultData);

    //处理
    $articleStatus = $sqlData['status'];
    switch ($resultData['code'])
    {
        case 1:
            $logData['status'] = 1;
            // echo "#Success! pushAPI.postDataForAPI  -->>   urlID = {$logData['urlID']}  post API is success , resultMessage => #".$resultData['message']."# ..<br/>".PHP_EOL;
            $articleStatus = 3;
            $statisticsInfo['pushAPI']['#Success']++;
            //记录发送状态
//            saveRepeatInfo($sqlData);
            break;
        case -10://重复数据
            $articleStatus = 10;
            $statisticsInfo['pushAPI']['#Repeat']++;
            break;
        default:
            if ($articleStatus < 4) {
                $articleStatus = 4;
            }else{
                if ($articleStatus < 7) {
                    $articleStatus++;
                }
            }
            echo "#Error! pushAPI.postDataForAPI  -->>   error code [{$logData['resultCode']}]!  urlID = {$logData['urlID']}  post API is failure , resultMessage => #".$resultData['message']."# ..<br/>".PHP_EOL;
            $statisticsInfo['pushAPI']['#Error']++;
            break;
    }

    //更新文章状态
    updateArticleStatus($urlID, $articleStatus);
//    //保存发送记录
//    savePushAPILog($logData);
}

function postDataTypeBy_3($sqlData)
{
    global $statisticsInfo;

    //处理API提交数据
    $videoInfo = unserialize($sqlData['content']);
    $postData = [
        'tag' => $sqlData['tag'],
        'type' => $sqlData['type'],
        'check' => $sqlData['check'],
        'videoTime' => $videoInfo['videoTime'],
        'description' => $videoInfo['description'],
        'videoID' => $videoInfo['videoID'],
        'url' => $sqlData['url'],
        'title' => $sqlData['title'],
        'status' => $sqlData['check'],
        'playTimes' => $videoInfo['playTimes'],
        'thumbnail' => $sqlData['thumbnail'],
        'videoUrl' => $videoInfo['cdn_video'],
        'size' => $videoInfo['size'],
        'like' => 0,
        'content' => ' ',
        'keywords' => $sqlData['keywords'],
    ];

    if ($videoInfo['size'] < 0){
        echo '#Error : '.$sqlData['urlID'].' video size = '.$videoInfo['size'].' ...'.PHP_EOL;return false;
    }

    //模拟点赞数据
//    $postData['like'] = rand(80,120);
    $postData['like'] = 0;

    // unset($sqlData['html']);
    // print_format($sqlData);
    // print_format($postData);die();

    //处理入库数据
    $urlID = $sqlData['urlID'];
    $logData['urlID'] = $urlID;
    $logData['type'] = $postData['type'];
    $logData['status'] = 0;
    $logData['url'] = $postData['url'];
    $logData['pushInfo'] = $postData;
    $logData['operatorID'] = $sqlData['operatorID'];
    $logData['date'] = date('Y-m-d');

    // print_format($logData);die();

    //数据合法验证
    if (!isset($resultData['code'])) {
        $resultData = checkPostData($postData, $urlID);
    }

    //排重验证
    if (!isset($resultData['code'])) {
        if (checkRepeat($sqlData)) {
            echo "#Warning : pushAPI.postDataForAPI  -->>   urlID[{$urlID}] is repeated ....<br/>" . PHP_EOL;
            $resultData = array('code' => -10, 'message' => '重复数据');
        }
    }

    //提交数据
    if(!isset($resultData['code'])) {
        $resultData = postDataToAPI($postData,$urlID);
    }

    if (!isset($resultData['code'])) {
        echo "#Error! pushAPI.postDataForAPI  -->>   result ( NUll ) !  urlID = {$logData['urlID']}  post API is failure , resultMessage => # NULL # ..<br/>".PHP_EOL;
        // echo json_encode($postData).PHP_EOL;
        return false;
    }

    //更新数据
    $logData['resultCode'] = $resultData['code'];
    $logData['resultMsg'] = $resultData['message'];

    // var_dump($resultData);

    //处理
    $articleStatus = $sqlData['status'];
    switch ($resultData['code'])
    {
        case 1:
            $logData['status'] = 1;
            // echo "#Success! pushAPI.postDataForAPI  -->>   urlID = {$logData['urlID']}  post API is success , resultMessage => #".$resultData['message']."# ..<br/>".PHP_EOL;
            $articleStatus = 3;
            $statisticsInfo['pushAPI']['#Success']++;
            //记录发送状态
//            saveRepeatInfo($sqlData);
            break;
        case -10://重复数据
            $articleStatus = 10;
            $statisticsInfo['pushAPI']['#Repeat']++;
            break;
        default:
            if ($articleStatus < 4) {
                $articleStatus = 4;
            }else{
                if ($articleStatus < 7) {
                    $articleStatus++;
                }
            }
            echo "#Error! pushAPI.postDataForAPI  -->>   error code [{$logData['resultCode']}]!  urlID = {$logData['urlID']}  post API is failure , resultMessage => #".$resultData['message']."# ..<br/>".PHP_EOL;
            $statisticsInfo['pushAPI']['#Error']++;
            break;
    }

    //更新文章状态
    updateArticleStatus($urlID, $articleStatus);
//    //保存发送记录
//    savePushAPILog($logData);
}

function postDataForAPIByUrlID($urlID='')
{
    //获取数据
    $sqlData = getBody($urlID);
    // print_format($sqlData);die();//调试
    //提交数据
    postDataForAPI($sqlData);
}

function checkPostData($postData, $urlID)
{
    $result = false;
    if (!isset($postData['type'])) {
        echo "#Error : PushAPI.checkPostData -> urlID[{$urlID}] >> type is NULL ....<br/>" . PHP_EOL;
        return $result = array('code' => -11, 'message' => '缺少type数据，默认为1');
    }
    if (!isset($postData['check'])) {
        echo "#Error : PushAPI.checkPostData -> urlID[{$urlID}] >> type is NULL ....<br/>" . PHP_EOL;
        return $result = array('code' => -11, 'message' => '缺少check数据');
    }
    if (!isset($postData['tag'])) {
        echo "#Error : PushAPI.checkPostData -> urlID[{$urlID}] >> tag is NULL ....<br/>" . PHP_EOL;
        return $result = array('code' => -12, 'message' => '缺少tag数据');
    }
    if (strlen($postData['url']) < 1) {
        echo "#Error : PushAPI.checkPostData -> urlID[{$urlID}] >> url is NULL ....<br/>" . PHP_EOL;
        return $result = array('code' => -13, 'message' => '缺少url数据');
    }
    if (strlen($postData['title']) < 1) {
        echo "#Error : PushAPI.checkPostData -> urlID[{$urlID}] >> title is NULL ....<br/>" . PHP_EOL;
        return $result = array('code' => -14, 'message' => '缺少title数据');
    }
//    if (strlen($postData['time']) < 1) {
//        echo "#Error : PushAPI.checkPostData -> urlID[{$urlID}] >> time is NULL ....<br/>" . PHP_EOL;
//        return $result = array('code' => -15, 'message' => '缺少time数据');
//    }
    if (strlen($postData['content']) < 1) {
        echo "#Error : PushAPI.checkPostData -> urlID[{$urlID}] >> content is NULL ....<br/>" . PHP_EOL;
        return $result = array('code' => -16, 'message' => '缺少content数据');
    }
    return $result;
}


function postDataToAPI($postData = array('type'=>0,'tag'=>0,'url'=>'','title'=>'','time'=>'','content'=>''),$urlID)
{
    global $sysConfig;
    $url = $sysConfig['api']['push_articles'];
//    print_format($postData,'$postData');
    $result = curlPost($url, $postData, $urlID);
    echo "#RESULT : {$result}  ,  url = {$url}". PHP_EOL;

    if ($result) {
        $resultData = jsonToArraySafe($result);
    }else{
        $resultData = array('code'=>0, 'message'=>'Null');
    }
    return $resultData;
}

function curlPost($url, $postData = array(),$urlID)
{
//    print_format($postData);die;
    $post = http_build_query($postData);
    echo "pushAPI::curlPost >>> urlID = ".sha1($postData['url'])." , tag = {$postData['tag']} , check = {$postData['check']} , like = {$postData['like']} , type = {$postData['type']}".PHP_EOL;

    $header = [
        'Content-Type:application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With:XMLHttpRequest',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode != 200) {
        try{
            global $dbo;
            $unid = date('Y-m-d').'/push';
            $sql = "SELECT un_id,info FROM alert_log WHERE un_id='{$unid}'";
            $check = $dbo->loadAssoc($sql);
            if(empty($check))
            {
                $dbo->exec("INSERT INTO alert_log VALUES('{$unid}','push_api_error','1','0')");
            }else{
                $dbo->exec("UPDATE alert_log SET info = info+1 WHERE un_id='$unid'");
            }
        }catch(Exception $error){
            echo ' try to handle the mysql but error'.PHP_EOL;
        }
        echo "#RESULT : HTTP_CODE = {$httpCode}  ...url = {$url}   \n{$result}". PHP_EOL;
    }

    return $result;
}








