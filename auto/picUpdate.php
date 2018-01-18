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


//配置变量加载
include_once(dirname(__FILE__).'/../conf/incloudeURL.php');

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
            $files = preg_replace('/http:\/\/cdn\.mobibookapp\.com\/comics\/file\/jpeg\//ism','',$file);
            $files = preg_replace('/http:\/\/cdn\.mobibookapp\.com\/comics\/file\/jpg\//ism','',$file);
            $files = preg_replace('/http:\/\/cdn\.mobibookapp\.com\/comics\/file\/gif\//ism','',$file);
            $files = preg_replace('/http:\/\/cdn\.mobibookapp\.com\/comics\/file\/png\//ism','',$file);
        }else{
            $files = preg_replace('/http:\/\/src\.mysada\.com\/sada\/file\/jpeg\//ism','',$file);
            $files = preg_replace('/http:\/\/src\.mysada\.com\/sada\/file\/jpg\//ism','',$file);
            $files = preg_replace('/http:\/\/src\.mysada\.com\/sada\/file\/gif\//ism','',$file);
            $files = preg_replace('/http:\/\/src\.mysada\.com\/sada\/file\/png\//ism','',$file);
        }
        

        $html = imageCropWhiteLaceByFile($value['pic'],'new.jpg');

        //如果图片流文件失本次则不覆盖更新
        $imgs = getimagesizefromstring($html);
        if(!$imgs[0]) continue;

        /*
            //下载图片到本地
            //$p = str_pad($a,5,'0',STR_PAD_LEFT).($k+1+$i);
            $chapter = $value['chapter'];
            $p = $value['page'];
            $file = './img/jinji/'.$chapter.'/'.$p.'.jpg';
            $dir = '\img\\/jinji\\'.$chapter.'\\';
            $dir1 = './img/jinji/'.$chapter.'/';

            echo $file.PHP_EOL;
            if(file_exists($file))
            {
                echo "当前目录中，文件".$file."存在".PHP_EOL;
                continue;
            }else{
                $html = imageCropWhiteLaceByFile($value['pic'],'new.jpg');
                if(!is_dir($dir1)){
                    mkdir($dir1, 777, true);
                }
                $dd = 'C:\wamp64\www\cartoon'.$file;
                copy('new.jpg',$dd);
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
                'fileName'=>$files,
            ];
        }else{
            //新闻
            $postData = [
                'appName'=>'sada',
                'type'=>'file',
                // 'w'=>610,
                // 'h'=>1000,
                'fileContent'=>base64_encode($html),
                'fileName'=>$files,
            ];
        }
        
        //下载内容图片
        $temp = (array)json_decode(srcPostAPI($postData));
        //var_dump($temp);

        
    }
    
   
}

/**
 * $imagePath   图片地址，支持本地路径和远程URL，可以直接使用CDN的图片地址。
 * $newPath     新的图片名字+路径。不写这个参数默认返回图片数据流，本地无残留，可以直接传CDN过去，原名文件名直接盖过去的话CDN会在24小时内更新资源。
 * $lace        默认留白边的宽度px，考虑到完全不留白边的阅读体验未必就很好，因此加入5像素白边
 * 大概说明：黑白图片裁切的比较严格，基本只要有留白都会裁掉，彩色的图片做了验证，如果裁切边距大于70px就放弃裁切。
*/
function imageCropWhiteLaceByFile($imagePath, $newPath = false, $lace = 5)
{
    $imageData = file_get_contents($imagePath);
    $imageInfo = imagecreatefromstring($imageData);

    //图片宽高
    $imageWidth = imagesx($imageInfo);
    $imageHeight = imagesy($imageInfo);

    //设定初始值
    $validX = $validY = 10000000;
    $validWidth = $validHeight = 0;
    $isColoursImage = false;
    $isUpdate = true;
    $xxx = 0;

    for ($x=0; $x < $imageWidth; $x++)
    { 
        for ($y=0; $y < $imageHeight; $y++)
        { 
            //提取像素色值并计算R,G,B的值
            $color_RGB = imagecolorat($imageInfo,$x,$y);
            $R = ($color_RGB >> 16) & 0xFF;
            $G = ($color_RGB >> 8) & 0xFF;
            $B = $color_RGB & 0xFF;

            //判断是否是彩色图
            if (abs($R - $G) > 50 || abs($B - $G)  > 50 || abs($B - $R) > 50) {
                $isColoursImage = true;
            }
            if ($isColoursImage && $xxx < 10) {
                //echo abs($R - $G)." , ".abs($B - $G)." , ".abs($B - $R)."\n";
                $xxx++;
            }

            // if ($R == 0 && $G == 0 && $B == 0)//全黑的容错率不行
            if ($R < 20 && $G < 20 && $B < 20)//这个条件容错率高一点
            {
                if ($validX > $x) {
                    $validX = $x;
                }
                if ($validY > $y) {
                    $validY = $y;
                }
                if ($validWidth < $x) {
                    $validWidth = $x;
                }
                if ($validHeight < $y) {
                    $validHeight = $y;
                }
            }
        }
    }

    //排除裁切过大的彩图
    if ($isColoursImage && ($validX > 100 || $validY > 100 || ($imageWidth - $validWidth) > 100 || ($imageHeight - $validHeight) > 100 )) {
        $isUpdate = false;
        echo "isColoursImage = $isColoursImage, x = $validX, y = $validY, width = $imageWidth - $validWidth, height = $imageHeight - $validHeight \n";
    }else{
        if ($validX < 5 && $validY < 5 && ($imageWidth - $validWidth) < 5 && ($imageHeight - $validHeight) < 5) {
            $isUpdate = false;
        }
    }

    if (!$isUpdate)
    {
        $newImage = $imageInfo;
    }
    else
    {
        $newWidth = $validWidth - $validX;
        $newHeight = $validHeight - $validY;

        // echo "x = $validX, y = $validY, newWidth = $newWidth, newHeight = $newHeight \n";

        //创建新画布
        $newImage = imagecreatetruecolor($newWidth + $lace*2, $newHeight + $lace*2);

        //原始画布填充为白色背景
        $color = imagecolorAllocate($newImage,255,255,255);
        imagefill($newImage,0,0,$color);

        //裁剪处理
        imagecopyresampled($newImage, $imageInfo, $lace, $lace, $validX, $validY, $newWidth, $newHeight, $newWidth, $newHeight);
    }
    
    //输出图片
    if ($newPath)
    {
        imagejpeg($newImage, $newPath);
        return file_get_contents('new.jpg');
        if ($isUpdate) {
            echo "#Suc : newPath = $newPath \n";
        }else{
            echo "#Suc <image not update>:  .... newPath = $newPath \n";
        }
    }else{
        if (!$isUpdate) {
            echo "#Notice : <image not update>\n";
        }
        return ['update'=>$isUpdate,'imageData'=>$newImage];
    }
}

print_format($statisticsInfo,'$statisticsInfo');








