<?php
defined('HOST_PATH') or exit("path error");

function checkRepeat($sqlData)
{
//    global $dbo;
//    $titleID = formatValueAndSha1($sqlData['title']);
//
//    $exist = $dbo->loadObject("SELECT 1 FROM `push_Repeat` WHERE `titleID` = '{$titleID}' LIMIT 1");
//    if ($exist) {
//        echo "#Notice : pushRepeatDB.checkRepeat  -->>   titleID = [{$titleID}] is existed ....<br/>" . PHP_EOL;
//        $dbo->exec("UPDATE `push_Repeat` SET `repeatTimes` = repeatTimes + 1 WHERE `titleID` = '{$titleID}'");
//        return true;
//    }
    return false;
}

function saveRepeatInfo($sqlData)
{
    // var_dump($sqlData);
//    global $dbo;
//    $titleID = formatValueAndSha1($sqlData['pushInfo']['title']);
//    $content =
//    $contentID = formatValueAndSha1(json_decode(base64_decode($sqlData['pushInfo']['content'])));
//
//    $sql = "INSERT INTO `push_Repeat` (`titleID`, `contentID`, `repeatTimes`) VALUES ('{$titleID}', '{$contentID}', 1)";
//    try {
//        $dbo->exec($sql);
//        echo "#Success : pushRepeatDB.saveRepeatInfo  -->>   titleID = {$titleID} , urlID = {$sqlData['urlID']}  insert ok ....<br/>" . PHP_EOL;
//    } catch (Exception $e) {
//        echo "#Error : pushRepeatDB.saveRepeatInfo  -->>   titleID = {$titleID} , urlID = {$sqlData['urlID']} insert error ....<br/>" . PHP_EOL;
//        echo "  #{$sql}" . PHP_EOL;
//    }
//    return true;
}

function formatValueAndSha1($value='')
{
    $patten[] = '/&[^;]*?;/i';
    $patten[] = '/<([^>]*?)>/i';
    $value = preg_replace($patten,'',$value);

    $pattenArr = array("\r\n", "\n", "\r", " ", "   ");
    $value = str_replace($pattenArr, "", $value);

    $value = trim($value);

    //防止超长数据sha1重复率过高
    if (strlen($value) > 500) {
        $value = mb_substr($value, 0, 500);
    }

    return sha1($value);
}

