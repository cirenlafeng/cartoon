<?php 
set_time_limit(7200);
//设定页面编码
header("Content-Type:text/html;charset=utf-8");
//设定时区
date_default_timezone_set('Asia/Shanghai');

error_reporting(E_ALL ^ E_NOTICE);

//禁止浏览器访问
if (PHP_SAPI != 'cli') {
    return phpinfo();
}
//命令行参数指定章节获取 
$flag = [];
if($argv[2] || $argv[3]){
    $flag[0] = (int) $argv[2];  #list_id
    $flag[1] = (int) $argv[3];   #章节
    $flag[2] = $argv[4];   #范围
}
if(!$flag[0]){
    echo "#List_id not null".PHP_EOL;
    return false;
}
if(!$flag[1]){
    echo "#chapter not null".PHP_EOL;
    return false;
}

if(isset($flag[2]) && (!is_string($flag[2]) || !preg_match('/,/ism',$flag[2]))){
    echo "#waring Range not string ----->Correct     1,100".PHP_EOL;
    return false;
}else{
    $tmp = explode(',',$flag[2]);
   if(count($tmp) == 2){
       $range = "`chapter` >= {$tmp[0]} and `chapter` <= {$tmp[1]}"; 
   }elseif(isset($flag[2])){
       echo "#waring Range error".PHP_EOL;
       return false;
   }
    
}


//全局加载
include_once(dirname(__FILE__).'/../conf/include.php');
include_once(dirname(__FILE__).'/grafika/src/autoloader.php');



//备选参数，针对某个站点单独执行
// $control = false;
// if (isset($argv[1])) {
//     $control = $argv[1];
//     $filePath = dirname(__FILE__).'/../control/'.$control.'.class.php';
//     if(!file_exists($filePath))
//     {
//         echo "not find file === ".$filePath.PHP_EOL;die();
//     }
// }


use Grafika\Grafika; // Import package
$editor = Grafika::createEditor(); // Create the best available editor

echo "<pre>".date('Y-m-d H:i:s').'<br/>'.PHP_EOL;
// var_dump($existFiles);
## Fetch start
use Ares333\CurlMulti\Core;
$stime = microtime(true);
$curl = new Core();
$curl->opt [CURLOPT_USERAGENT]      = getUserAgentInfo();
$curl->opt [CURLOPT_HTTPHEADER]     = getUserIP();
$curl->opt [CURLOPT_REFERER]        = getUserReferer();
$curl->opt [CURLOPT_SSL_VERIFYPEER] = FALSE;//不验证SSL
$curl->opt [CURLOPT_SSL_VERIFYHOST] = FALSE;//不验证SSL
$curl->cbTask = array('work');
$curl->maxThread = 20;//线程数
$curl->maxTry = 2;//失败重试
$curl->start();
$etime = microtime(true);
echo "Finished in .. ". round($etime - $stime, 3) ." seconds\n";

function work()
{
    global $curl;
    global $range;
    global $flag;
    global $dbo;

    //获取去白边的书籍
    if($range){
        $countSql = "SELECT count(1) as count FROM `comics_chapters` WHERE `status` = 8 and `list_id`= '{$flag[0]}' and ".$range." LIMIT 5000";
        $sql = "SELECT id,chapter,page,pic FROM `comics_chapters` WHERE `status` = 8 and `list_id`= '{$flag[0]}' and ".$range." ORDER BY chapter LIMIT 5000";
    }else{
        $countSql = "SELECT count(1) as count FROM `comics_chapters` WHERE `status` = 8 and `list_id`= '{$flag[0]}' and `chapter` LIKE '{$flag[1]}' LIMIT 5000";
        $sql = "SELECT id,chapter,page,pic FROM `comics_chapters` WHERE `status` = 8 and `list_id`= '{$flag[0]}' and `chapter` LIKE '{$flag[1]}' ORDER BY chapter LIMIT 5000";
    }
    $row = $dbo->loadObject($countSql);
    $count =$row->count;
    echo "#SQL :: {$sql}".PHP_EOL;
    echo '#Working start : articles count = '.$row->count.'    Wait a minute ..............' . PHP_EOL;
    $articles = $dbo->loadAssocList($sql);

    //合并相同章节
    $res = array();
    foreach ($articles as $v) {
        $k = (int) $v['chapter'];
        $res[$k] = $v;
    }
    $res = array_merge($res);
    $info = array();
    foreach ($res as $k=>$value) {
        foreach ($articles as $v) {
            if($v['chapter'] == $value['chapter']){
                $info[$k][] = $v;
            }
        }
        
    }
    foreach ($info as $k => $value) {
        $callFunName = 'www_manga_ae_Funtion';
        $curl->add([
                    'url' => 'https://www.baidu.com/',
                    'args' => $info[$k],
                ], $callFunName);
        echo "#CURL : ".$k . PHP_EOL;
    }
    


    

    $curl->cbTask = null;
}

function www_manga_ae_Funtion($result, $args)
{
    //https://cdn.mobibookapp.com/comics/file/jpg/
    foreach ($args as $key => $value) {
        $file = $value['pic'];
        if(preg_match('/cdn\.mobibookapp\.com/ism',$file)){
            $file = preg_replace('/http:\/\/cdn\.mobibookapp\.com\/comics\/file\/jpeg\//ism','',$file);
            $file = preg_replace('/http:\/\/cdn\.mobibookapp\.com\/comics\/file\/jpg\//ism','',$file);
            $file = preg_replace('/http:\/\/cdn\.mobibookapp\.com\/comics\/file\/gif\//ism','',$file);
            $file = preg_replace('/http:\/\/cdn\.mobibookapp\.com\/comics\/file\/png\//ism','',$file);
        }else{

            $file = preg_replace('/http:\/\/src\.mysada\.com\/sada\/file\/jpeg\//ism','',$file);
            $file = preg_replace('/http:\/\/src\.mysada\.com\/sada\/file\/jpg\//ism','',$file);
            $file = preg_replace('/http:\/\/src\.mysada\.com\/sada\/file\/gif\//ism','',$file);
            $file = preg_replace('/http:\/\/src\.mysada\.com\/sada\/file\/png\//ism','',$file);
        }
        
        copy($value['pic'],'old.jpg');
        $html1 = file_get_contents('old.jpg');
        $html = imageCropWhiteLaceByFile('old.jpg');
        $imgs = getimagesizefromstring($html);
        $imgs1 = getimagesizefromstring($html1);
		if(!$imgs[0] || !$imgs1[0]){
			echo '#Warning'.'-->'.$value['pic'].PHP_EOL;
			continue;

		}


        //如果图片流文件失本次则不覆盖更新
        // $imgs = getimagesizefromstring($html);
        // if(!$imgs[0]) continue;

        /*
            //下载图片到本地
            //$p = str_pad($a,5,'0',STR_PAD_LEFT).($k+1+$i);
            $chapter = $value['chapter'];
            $p = $value['page'];
            $file = './img/huoying/'.$chapter.'/'.$p.'.jpg';
            $dir = '\img\\/huoying\\'.$chapter.'\\';
            $dir1 = './img/huoying/'.$chapter.'/';

            echo $file.PHP_EOL;
            if(file_exists($file))
            {
                echo "当前目录中，文件".$file."存在".PHP_EOL;
                continue;
            }else{
          //   	copy($value['pic'],'old.jpg');
          //       $html = imageCropWhiteLaceByFile('old.jpg');
          //       $imgs = getimagesizefromstring($html);
        		// if(!$imgs[0]) continue;
                if(!is_dir($dir1)){
                    mkdir($dir1, 777, true);
                }
                $dd = 'C:\wamp64\www\cartoon'.$file;
                copy('brightness.jpg',$dd);
            }
            
            */
        /*

        //阅读
        // $postData = [
        //     'appName'=>'comics',
        //     'type'=>'comics_manga_img',
        //     // 'w'=>$w,
        //     // 'h'=>$h,
        //     'srcUrl'=>$html,
        //     'fileName'=>$t.md5($data['list_id'].'_'.$data['chapter']).$data['list_id'].'_'.$data['page'].'_'.substr(strrchr($data['thumbnail'], '/'),1),
        // ];
        */
        
        if(preg_match('/cdn\.mobibookapp\.com/ism',$file)){
            $postData = [
                'appName'=>'comics',
                'type'=>'file',
                // 'w'=>$w,
                // 'h'=>$h,
                'fileContent'=>base64_encode($html),
                'fileName'=>$file,
            ];
        }else{
            //新闻
            $postData = [
                'appName'=>'sada',
                'type'=>'file',
                // 'w'=>610,
                // 'h'=>1000,
                'fileContent'=>base64_encode($html),
                'fileName'=>$file,
            ];
        }
        
        //下载内容图片
        $temp = (array)json_decode(srcPostAPI($postData));
        var_dump($temp);

        
    }
    
   
}

/**
*	图片亮度对比度调节  10+20
*/



function imageCropWhiteLaceByFile($pic)
{
	global $editor;

	$editor->open( $image,$pic);
	$filter = Grafika::createFilter('Brightness', 10);
	$filter = Grafika::createFilter('Contrast', 20);
	$editor->apply( $image, $filter );
	$info = $editor->save($image,'brightness.jpg');
	if($info){
		echo "#Success:".$a.'.jpg->亮度调节成功'.PHP_EOL;
		return file_get_contents('brightness.jpg');
	}else{
		echo "#Warning:".$a.'.jpg->亮度调节失败'.PHP_EOL;
	}
}

print_format($statisticsInfo,'$statisticsInfo');









