<?php
error_reporting(E_ALL ^ E_NOTICE);
//全局加载
include_once(dirname(__FILE__).'/conf/include.php');
include_once(dirname(__FILE__).'/lib/phpExcelReader/Excel/reader.php');

$siteUrlData = array();
$work = array();


$filePath = dirname(__FILE__).'/temp/mysada_sources.xls';

$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
$data->read($filePath);

for ($tab=0; $tab < count($data->sheets); $tab++)
{
    for ($i = 2; $i <= $data->sheets[$tab]['numRows']; $i++)
    {
        $temp['url'] = trim($data->sheets[$tab]['cells'][$i][1]);
        if (strlen($temp['url']) > 10)
        {
            $temp['tag'] = (int)trim($data->sheets[$tab]['cells'][$i][2]);
            $temp['type'] = (int)trim($data->sheets[$tab]['cells'][$i][3]);
            $temp['check'] = (int)trim($data->sheets[$tab]['cells'][$i][4]);
            $temp['weight'] = (int)trim($data->sheets[$tab]['cells'][$i][5]);
            $temp['isDone'] = (int)trim($data->sheets[$tab]['cells'][$i][6]);

            $domain = getDomainThis($temp['url']);

            $siteUrlData[$domain][] = $temp;
            $work[$domain] = $temp['weight'];
        }
    }
}

print_r($siteUrlData);

$fileInfo = '<?php'."\n\n";
foreach ($siteUrlData as $domain => $urls)
{
    // echo '$urlInfo[\''.$domain.'\'] = ['."\n";
    $fileInfo .= '$urlInfo[\''.$domain.'\'] = ['."\n";
    foreach ($urls as $key => $urlData)
    {
        $fileInfo .= "	#{$key}\n";
        $fileInfo .= "	[\n";
        foreach ($urlData as $key2 => $value)
        {
            $fileInfo .= "		'{$key2}' => '{$value}',\n";
        }
        $fileInfo .= "	],\n";
    }
    // echo "];\n";
    $fileInfo .= "];\n\n\n";
}

$userInfoFile = $fileInfo;

//初始化db
$dbo = dbo('webEngine');


//输出 SELECT * FROM `schedule` ORDER BY `weight` ASC, `domain` ASC LIMIT 5000
$sql = "SELECT * FROM `schedule` ORDER BY `weight` ASC, `domain` ASC LIMIT 5000";
$rows = $dbo->loadAssocList($sql);

$newInsert = false;
foreach ($work as $domian => $value)
{
    $type = 0;
    foreach ($rows as $row)
    {
        if ($row['domain'] == $domian)
        {
            $type = 1;
            if ($row['weight'] != $value) {
                $type = 2;
            }
        }
    }
    switch ($type)
    {
        case 0:
            $sql = "INSERT INTO `schedule` (`domain`, `weight`, `operationDate`) VALUES ('".$domian."', ".$value.", '".date('Y-m-d')."')";
            try {
                $dbo->exec($sql);
                $newInsert = true;
            } catch (Exception $e) {
                echo "#ERROR : ---->>> \$work[".$value['domain']."] = ".$work[$value['domain']]." \n #{$sql} \n" . PHP_EOL;
                return false;
            }
            break;
        case 2:
            $sql = "UPDATE `schedule` SET `weight` = {$value} WHERE `domain` = '{$domian}'";
            try {
                $dbo->exec($sql);
                $newInsert = true;
            } catch (Exception $e) {
                echo "#ERROR : ---->>> \$work[".$value['domain']."] = ".$work[$value['domain']]." \n #{$sql} \n" . PHP_EOL;
                return false;
            }
            break;
        default:
            break;
    }
}

//重新查询
if ($newInsert) {
    $sql = "SELECT * FROM `schedule` ORDER BY `weight` ASC, `domain` ASC LIMIT 5000";
    $rows = $dbo->loadAssocList($sql);
}

$workFile = "\n\n";
foreach ($rows as $value) {
    $workFile .= '$work'."['".$value['domain']."'] = ".$value['status'].";		# ".$value['notes'].' , weight = '.$value['weight'].", \n";
}
$workFile .= "\n\n\n";
$fileInfo .= $workFile;
$workFile = '<?php'.$workFile;

$operatorFile = "\n\n";
foreach ($rows as $value) {
    $operatorFile .= '$operatorID'."['".$value['domain']."'] =	".$value['operatorID'].";\n";
}
$operatorFile .= "\n\n\n";
$fileInfo .= $operatorFile;
$operatorFile = '<?php'.$operatorFile;



//写入文件
$myfile = fopen("urlInfo".date('Y-m-d').".php", "w") or die("Unable to open file!");
fwrite($myfile, $fileInfo);
fclose($myfile);

//$userInfoFile
$myfile = fopen("conf/urlInfo.php", "w") or die("Unable to open file!");
fwrite($myfile, $userInfoFile);
fclose($myfile);

//$operatorFile
//$myfile = fopen("conf/operatorID.php", "w") or die("Unable to open file!");
//fwrite($myfile, $operatorFile);
//fclose($myfile);

//$workFile
//$myfile = fopen("conf/schedule.php", "w") or die("Unable to open file!");
//fwrite($myfile, $workFile);
//fclose($myfile);


//数序查看 


//获取域名
function getDomainThis($url, $http = '')
{
    $temp = parse_url($url);
    $urlAll = $http.$temp['host'];
    return $urlAll;
}













