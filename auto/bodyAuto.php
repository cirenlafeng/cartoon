<?php 
set_time_limit(7200);
//设定页面编码
header("Content-Type:text/html;charset=utf-8");
//设定时区
date_default_timezone_set('Asia/Shanghai');

error_reporting(E_ALL ^ E_NOTICE);

//禁止浏览器访问
// if (PHP_SAPI != 'cli') {
//     return phpinfo();
// }

//全局加载
include_once(dirname(__FILE__).'/../conf/include.php');


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

//提取所有业务文件
$filesNames = scandir(HOST_PATH.'/control/');
// var_dump($filesNames);die();

//配置变量加载
include_once(dirname(__FILE__).'/../conf/incloudeURL.php');

//载入所有业务
foreach ($filesNames as $key => $fileName)
{
	if (strpos($fileName, '.class.php')) {
		include_once(HOST_PATH.'/control/'.$fileName);
	}
}

include_once(dirname(__FILE__).'/../conf/urlInfo.php');
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
    $curl->maxThread = 10;//线程数
    $curl->maxTry = 2;//失败重试
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
	global $dbo;
	global $control;
    $dateTime3 = time() - (86400*3);
    $countSql = '';
    $sql = '';
    $countSql = "SELECT count(1) as count FROM `comics_chapters` WHERE `status` = 0 and `create_time` >= '{$dateTime3}' LIMIT 8000";
	$sql = "SELECT * FROM `comics_chapters` WHERE `status` = 0 and `create_time` >= '{$dateTime3}' LIMIT 8000 ";
	$row = $dbo->loadObject($countSql);
	$count =$row->count;
	echo "#SQL :: {$sql}".PHP_EOL;
	echo '#Working start : articles count = '.$row->count.'    Wait a minute ..............' . PHP_EOL;
	$articles = $dbo->loadAssocList($sql);
	foreach ($articles as $key => $article)
	{
		//获得回掉函数名
        $callFunName = getControlFunFirstName($article['domain']).'_Funtion';
        $article['fun'] = 'getBody';
        //特殊设置
    	//$curl = setCurlOPT($article['domain'], $curl);
		switch ($article['status'])
		{
			#没采集的Html
			case 0:
				{
					if (strlen($article['thumbnail']) > 5) {
						$curl->add([
						    'url' => $article['thumbnail'],
						    'args' => $article,
						], $callFunName);
						$curl->cbTask = null;
//								echo '#URL : '.$article['url'].PHP_EOL;
					}
				}
				break;
			default:
			    echo 'status is Error : '.$article['status'].PHP_EOL;
				# code...
				break;
		}
	}
}

print_format($statisticsInfo,'$statisticsInfo');


// print_format($urlInfo, '$urlInfo');





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

