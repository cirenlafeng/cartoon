<?php
defined('HOST_PATH') or exit("path error");


function savePushAPILog($sqlData)
{
    if (!is_array($sqlData)) {
        echo "#Error : pushAPILogDB.savePushAPILog  -->>   sqlData is not array() ....<br/>" . PHP_EOL;return false;
    }

    global $dbo;
    extract($sqlData);
    $pushInfo = base64_encode(json_encode($pushInfo));

    $sql = "INSERT INTO `push_API_Log` (`urlID`, `type`, `status`, `url`, `pushInfo`, `resultCode`, `resultMsg`, `operatorID`, `date`) VALUES ('{$urlID}', {$type}, {$status}, '{$url}', '{$pushInfo}', {$resultCode}, '{$resultMsg}', 0, '{$date}')";
    // echo "#SQL : $sql\n<br/>";die();
    $result = true;
    try {
        $dbo->exec($sql);
    } catch (Exception $e) {
        echo "#Error : pushAPILogDB.savePushAPILog  -->>   urlID = [{$urlID}]  ....<br/>" . PHP_EOL;
        echo "#SQL :  {$sql} <br/>" . PHP_EOL;
        $result = false;
    }
}

function getPushAPILogByID($urlID='')
{
    global $dbo;
    $result = $dbo->loadAssoc("SELECT * FROM `push_API_Log` WHERE `urlID` = '{$urlID}' LIMIT 1");
    if ($result) {
        $result['pushInfo'] = json_decode(base64_decode($result['pushInfo']));
        return $result;
    }else{
        return false;
    }
}






