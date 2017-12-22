<?php 
set_time_limit(3600);
//设定页面编码
header("Content-Type:text/html;charset=utf-8");
date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL ^ E_NOTICE);

//禁止浏览器访问
if (PHP_SAPI != 'cli') {
    return phpinfo();
}

if($argv[1]){
    $flag = (int) $argv[1];
}else{
    $flag = 1;
}

include_once(dirname(__FILE__).'/../conf/include.php');
//提取所有业务文件
$filesNames = scandir(HOST_PATH.'/control/');
// var_dump($filesNames);die();
$fileType = '.class.php';
$existFiles = array();
//备选参数，针对某个站点单独执行
$control = false;
if (isset($argv[1])) {
    $control = $argv[1];
    $filePath = dirname(__FILE__).'/../control/'.$control.'.class.php';
    if(!file_exists($filePath))
    {
        echo "not find file === ".$filePath.PHP_EOL;die();
    }
}

//全站业务
$urlInfo['www.manga.ae'] = [];
$urlTemp = 'https://www.manga.ae/manga/page:';
for ($i=1; $i <= 30; $i++) { 
    $urlForTemp = $urlTemp.$i;
    $html = BypassCloudFlare($urlForTemp);
    phpQuery::newDocumentHTML($html);
    $articles = pq('div.mangacontainer');
    foreach ($articles as $k=>$article)
    {
        echo $k.PHP_EOL;
        $tag = '';
        //详情页地址获取,用于抓取标签
        $detail = pq($article)->find('a.manga:eq(0)')->attr('href');
        $html1 = BypassCloudFlare($detail);
        phpQuery::newDocumentHTML($html1);
        $detail = pq('div.indexcontainer');
        $list = pq($detail)->find('.manga-details-extended ul li');
        foreach ($list as $val)
        {
            $tmp = pq($val)->find('a:eq(0)')->text();
            if(!empty($tmp)){
                $tag .= ','.$tmp;
            }
            
        }
        $tags = trim($tag,',');                                                  #标签

        $introduce = pq($detail)->find('.manga-details-extended h4:eq(2)')->text();
        $introduce = mb_substr($introduce,0,1000);                                              #描述
        $introduce = str_replace("'","\'",$introduce);
        $author = pq($detail)->find('.manga-details-author h4:eq(0) a')->text();                #作者
         //书籍是否连载
        $status = pq($detail)->find('div.manga-details-extended h4:eq(1)')->text();
        if(preg_match('/مكتملة/',$status)){
            $status =0;
        }else{
            $status = 1;
        }

        $number = (int) pq($article)->find('div.details:eq(0)')->text();                        #参与人数
        $name = pq($article)->find('a.manga:eq(1)')->text();
        $name = str_replace("'","",$name);
        $url = pq($article)->find('a.manga:eq(1)')->attr('href');
        $count = pq($article)->find('div.details:eq(1) > a')->text();
        $year = pq($article)->find('div.year')->text();
        $pic = pq($article)->find('img:eq(0)')->attr('src');

        $count = empty($count) ? 0 : $count;
        $year = empty($year) ? 0 : $year;
        $pic = empty($pic) ? '' : $pic;

        
        if(!empty($name) && !empty($url))
        {
            $row = $dbo->loadAssoc("SELECT `id`,`name`,`chapters_count`,`update_time` FROM `comics_list` WHERE `name` LIKE '{$name}'");
            //判断是否有该书数据
            if($row)
            {
                //判断是否有更新
                if($count > $row['chapters_count'])
                {
                    if(empty($row['update_time'])){
                        $tmp=[];
                        $tmp[] = time();
                        $update_day = 0;
                    }else{
                        //记录每次更新时间求平均值
                        $tmp = unserialize($row['update_time']);
                        $tmp[] = time();
                        $num = count($tmp)-1;
                        foreach ($tmp as $t) {
                            $i = $num-1;
                            $day = $tmp[$num] - $tmp[$i];
                            $update_day = ceil($day/86400);
                        }
                        
                    }
                    $update_time = serialize($tmp);
                    $re = $dbo->exec("UPDATE `comics_list` SET `chapters_count` = '{$count}',`update_time` = '{$update_time}',`update_day` = '{$update_day}' WHERE `name` = '{$name}' AND `url`='{$url}' ");
                    if($re)
                    {
                        echo "##更新书籍：ID->".$row['id']." 名称：".$row['name'].PHP_EOL;
                        $addArr = [];
                        $addArr['url'] = $url;
                        $addArr['list_id'] = $row['id'];
                        $urlInfo['www.manga.ae'][] = $addArr;
                    }
                }
                /*else{
                    //本书章节没拿完则继续
                    $row1 = $dbo->loadAssocList("SELECT chapter FROM `comics_chapters` WHERE `list_id`='14' GROUP BY `chapter`");
                    if($count > count($row1)){
                        echo "##更新书籍：ID->".$row['id']." 名称：".$row['name'].PHP_EOL;
                        $addArr = [];
                        $addArr['url'] = $url;
                        $addArr['list_id'] = $row['id'];
                        $urlInfo['www.manga.ae'][] = $addArr;
                    }
                }
                */
                

            }else{
                $pic = imgIcon($pic,$name);
                $temp = [];
                $temp[] = time();
                $update_time = serialize($temp);
                $rel = $dbo->exec("INSERT INTO `comics_list` (`tags`,`name`,`author`,`pic`,`chapters_count`,`year`,`url`,`introduce`,`number`,`status`,`update_time`) VALUES('{$tags}','{$name}','{$author}','{$pic}','{$count}','{$year}','{$url}','{$introduce}','{$number}','{$status}','{$update_time}')");
                $list_id = $dbo->loadAssoc("SELECT `id`,`name` FROM `comics_list` WHERE `name` = '{$name}' AND `url`='{$url}' ");
                if($rel && !empty($list_id['id']))
                {
                    echo "##录入新的书籍：ID->".$list_id['id']." 名称：".$list_id['name'].PHP_EOL;
                    $addArr = [];
                    $addArr['url'] = $url;
                    $addArr['list_id'] = $list_id['id'];
                    $urlInfo['www.manga.ae'][] = $addArr;
                }else{
                    $dbo->exec("DELETE FROM `comics_list` WHERE `name` = '{$name}' AND `url`='{$url}' ");
                }
            }
        }
    }
    phpQuery::unloadDocuments();
}

function imgIcon($pic,$name){
    $t = time(); 
    $html = BypassCloudFlare($pic);
    $postData = [
        'appName'=>'sada',
        'type'=>'file',
        'w'=>610,
        'h'=>1000,
        'fileContent'=>base64_encode($html),
        'fileName'=>$t.md5($name).substr(strrchr($pic, '/'),1),
    ];
    
    //下载内容图片
    $temp = (array)json_decode(srcPostAPI($postData));
    if (!empty($temp['content'])) {
        echo "#Succes : get cdn succes  --->  BookId : {$name}".PHP_EOL;
        return $temp['content'];
    }else{
        echo "#Error : get cdn error  --->  BookId : {$name}".PHP_EOL;
        return false;
    }
}

//单本列表业务
$new_urlInfo = [];
foreach($urlInfo['www.manga.ae'] as $urlData)
{
    $html = BypassCloudFlare($urlData['url']);
    phpQuery::newDocumentHTML($html);
    $articles = pq('ul.new-manga-chapters > li');
    $sqlData = array();
    foreach ($articles as $article)
    {
        $url = pq($article)->find('a.chapter')->attr('href');
        $url = substr($url,0,strlen($str)-2).'0/full';
        $keyword = pq($article)->find('a.chapter')->text();
        $keyword = substr(strrchr($keyword, ':'),1);
        $keyword = str_replace("'","",$keyword);
        $chapter = findNum(substr(pq($article)->find('a.chapter')->text(),0,5));
        if(empty($keyword))
        {
            $keyword = '-';
        }
        if($chapter)
        {
            $exist = $dbo->loadObject("SELECT 1 FROM `comics_chapters` WHERE `list_id` = '{$urlData['list_id']}' AND `status`=8 AND `chapter`= '{$chapter}' LIMIT 1");
            if ($exist) 
            {
                echo "#跳过 : 书籍ID= {$urlData['list_id']} , 章节= {$chapter} 已提交系统 ..." . PHP_EOL;
                continue;
            }
        }
        if (strlen($url) > 10)
        {
            $new_urlData = $urlData;
            $new_urlData['url'] = $url;
            $new_urlData['chapter_name'] = $keyword;
            $new_urlInfo['www.manga.ae'][] = $new_urlData;
        }else{
            continue;
        }
    }
    phpQuery::unloadDocuments();
}
// print_r($new_urlInfo);die();
$urlInfo = $new_urlInfo;
// print_r($urlInfo);die();
//载入所有业务
foreach ($filesNames as $key => $fileName)
{
	if (strpos($fileName, $fileType)) {
		include_once(HOST_PATH.'/control/'.$fileName);
        $temp = explode('.', $fileName);
        $fileFirstName = '';
        for ($i=0; $i < count($temp) - 2; $i++) { 
            $fileFirstName = $fileFirstName . $temp[$i] . '.';
        }
        $existFiles[trim($fileFirstName,'.')] = true;
	}
}

//配置变量加载
include_once(dirname(__FILE__).'/../conf/incloudeURL.php');
use Ares333\CurlMulti\Core;
$url = 'https://www.manga.ae/naruto/698/0/full';
if('error' != BypassCloudFlare($url)){

    echo "<pre>".date('Y-m-d H:i:s').'<br/>'.PHP_EOL;
    $headers   = array();
    $headers[] = 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36';
    $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
    $headers[] = 'Accept-Language: ar,en;q=0.5';
    $headers[] = 'Connection: keep-alive';
    $stime = microtime(true);
    $curl = new Core();
    $curl->opt [CURLOPT_REFERER] = 'https://www.manga.ae/';
    $curl->opt [CURLOPT_ENCODING] = '';
    $curl->opt [CURLOPT_RETURNTRANSFER] = TRUE;
    $curl->opt [CURLOPT_FOLLOWLOCATION] = TRUE;
    $curl->opt [CURLOPT_COOKIEJAR] = dirname(__FILE__).'/'.parse_url($url, PHP_URL_HOST).'-cookie.txt';
    $curl->opt [CURLOPT_COOKIEFILE] = dirname(__FILE__).'/'.parse_url($url, PHP_URL_HOST).'-cookie.txt';
    $curl->opt [CURLOPT_HTTPHEADER] = $headers;
    $curl->opt [CURLOPT_HEADER] = FALSE;
    $curl->opt [CURLOPT_SSL_VERIFYPEER] = FALSE;
    $curl->opt [CURLOPT_SSL_VERIFYHOST] = FALSE;
    $curl->cbTask = array('work');
    $curl->maxThread = 16;//线程数
    $curl->maxTry = 1;//失败重试
    $curl->start();
    $etime = microtime(true);
    echo "Finished in .. ". round($etime - $stime, 3) ." seconds\n";

}else{
    echo "error".PHP_EOL;die();
}

function work()
{
	global $curl;
	global $urlInfo;
    global $existFiles;

    $randUrls = array();
    foreach ($urlInfo as $domain => $urls)
    {
        if (isset($existFiles[$domain]))
        {
            foreach ($urls as $key => $urlData)
            {
                $urlData['domain'] = $domain;
                $urlData['fun'] = 'getUrl';
                $randUrls[] = $urlData;
            }
        }
    }
    foreach ($randUrls as $key => $urlData)
    {
        //获得回掉函数名
        $callFunName = getControlFunFirstName($urlData['domain']).'_Funtion';

        //特殊设置
        //$curlOne = setCurlOPT($urlData['domain'], $curl);
        
    	$curl->add([
        		    'url' => $urlData['url'],
        		    'args' => $urlData,
                    'opt' => $curlOne->opt,
        		], $callFunName);
        echo "#CURL : ".$urlData['url'] . PHP_EOL;
    }
    echo "#RUNING : get url count ( ".count($randUrls)." )". PHP_EOL;
    $curl->cbTask = null;
}


print_format($statisticsInfo,'$statisticsInfo');




//绕过验证：

// the main function to bypass the CloudFlare
    function BypassCloudFlare($url) {
        $data = OpenURLCloudFlare($url);
        // print($data);die();
        if($data) {
            preg_match('/name="jschl_vc"\s+value="(.+)"/Ui', $data, $jschl_vc);
            preg_match('/name="pass"\s+value="(.+)"/Ui', $data, $pass);
            preg_match('/var.+:\+(.+)};/Uis', $data, $matches1);
            preg_match_all('/(\*|\+|\-)=(.+);/Uis', $data, $matches2);

            if(isset($matches1[1]) && isset($jschl_vc[1]) && isset($pass[1])) {
                $var = CalJSData($matches1[1]);
                foreach ($matches2[0] as $key => $value) {
                    if($matches2[1][$key] == '*') {
                        $var *= CalJSData($matches2[2][$key]);
                    } elseif($matches2[1][$key] == '-') {
                        $var -= CalJSData($matches2[2][$key]);
                    } elseif ($matches2[1][$key] == '+') {
                        $var += CalJSData($matches2[2][$key]);
                    }
                }
                $jschl_answer =  intval($var) + strlen(parse_url($url, PHP_URL_HOST));
                $url2  = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST);
                $url2 .= '/cdn-cgi/l/chk_jschl?';
                $url2 .= 'jschl_vc='.$jschl_vc[1];
                $url2 .= '&pass='.$pass[1];
                $url2 .= '&jschl_answer='.$jschl_answer;
                sleep(4);
                $data = OpenURLCloudFlare($url2, $url);
            }
        } else {
            return 'error';
        }
        return $data;
    }
    // fetch the page
    function OpenURLCloudFlare($url, $referer='https://www.manga.ae') {
        $headers   = array();
        $headers[] = 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36';
        $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
        $headers[] = 'Accept-Language: ar,en;q=0.5';
        $headers[] = 'Connection: keep-alive';
        $ch = curl_init();    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if($referer)
            curl_setopt($ch, CURLOPT_REFERER, $referer); 
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__).'/'.parse_url($url, PHP_URL_HOST).'-cookie.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__).'/'.parse_url($url, PHP_URL_HOST).'-cookie.txt');
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
            die();
        }
        curl_close($ch);
        return($data);
    }
    // parse Java script challenge
    function CalJSData($data) {
        $data       = str_replace(array('((', '))'), '', $data);
        $data       = explode('+[])+(', $data);
        $data[0]    = str_replace(array('!+[]', '!![]', '![]', '[]'), array('1', '1', '', ''), $data[0]);
        if(isset($data[1])) {
            $data[1]    = str_replace(array('!+[]', '!![]', '![]', '[]'), array('1', '1', '', ''), $data[1]);
            return substr_count($data[0], '1') .  substr_count($data[1], '1');
        }
        return substr_count($data[0], '1');
    }
// print_format($urlInfo, '$urlInfo');



function findNum($str=''){
    $str=trim($str);
    if(empty($str)){return '';}
    $result='';
    for($i=0;$i<strlen($str);$i++){
        if(is_numeric($str[$i]) || $str[$i] == '.'){
            $result.=$str[$i];
        }
    }
    return $result;
}


