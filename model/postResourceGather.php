<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2017/3/13
 * Time: 下午12:22
 */


#S3资源下载API地址：本机调试需要做host
defined('RRC_GATHER_URL') or define('RRC_GATHER_URL', $sysConfig['RRC_GATHER_URL']);


/**
 * 将文章内图片地址，采集上传CDN
 * @param $content
 * @return mixed
 */
function getContentImages($content, $appName = 'test', $width = 640, $height = 640)
{
    global $statisticsInfo;
    //提取所有img html，和最新img url
    $replaceArr = array();
    preg_match_all('#<img.*?src="([^"]*)"[^>]*>#i', $content, $match);
//    print_format($match,'$match');
    $count = count($match[1]);
    $getImgCount = 0;
    for ($i = 0; $i < $count; $i++)
    {
        $temp = [];
        $temp['find'] = $match[0][$i];
        $imgUrl = $match[1][$i];
        echo 'imgURL : '.$imgUrl.PHP_EOL;
        #第一次尝试
        $newImg = getSrcByImageCDN($imgUrl, $appName, $width, $height);
        #失败二次重试
        if ($imgUrl == $newImg){
            $newImg = getSrcByImageCDN($imgUrl, $appName, $width, $height);
        }
        #失败三次重试
        if ($imgUrl == $newImg){
            $newImg = getSrcByImageCDN($imgUrl, $appName, $width, $height);
        }
        if ($imgUrl != $newImg){
            $getImgCount++;
            $statisticsInfo['srcDownload']['success']['count']++;
        }else{
            $statisticsInfo['srcDownload']['Error'][] = $imgUrl;
        }
        $temp['replace'] = '<img width="100%" src="'.$newImg.'" />';
        $replaceArr[] = $temp;
    }
    if ($getImgCount < $count){
        return array('status'=>0,'content'=>$content,'imgList'=>$replaceArr);
    }

    foreach ($replaceArr as $replace)
    {
        $content = str_replace($replace['find'],$replace['replace'],$content);
    }

    return array('status'=>1,'content'=>$content,'imgList'=>$replaceArr);
}

/**
 * 调用API保存远程图片并推送到CDN
 * @param $srcURL
 * @param string $appName 应用名称
 * @param int $width 图片宽高
 * @param int $height
 * @param string $ext 图片新扩展名
 * @return mixed
 */
function getSrcByImageCDN($srcURL, $appName = 'test', $width=0, $height=0, $ext='')
{
    $data = [
        'srcUrl'=>$srcURL,
        'appName'=>$appName,
        'type'=>'image',
        'w'=>$width,
        'h'=>$height,
        'extension' =>$ext
    ];
//    print_format($data,'postData');

    $temp = (array)json_decode(srcPostAPI($data));
    if (!empty($temp['content'])) {
        return $temp['content'];
    }else{
        return $srcURL;
    }
}




function srcPostAPI($postData = array())
{
    $post = http_build_query($postData);

    $url = RRC_GATHER_URL;

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
        $result = json_encode(['code'=>-1,'msg'=>'HTTP_CODE = '.$httpCode]);
        echo 'CDN_error: --> '.$result.PHP_EOL;
    }else{
        $result = '{'.getPregData('/{(.*?)}/i',$result).'}';
        echo 'srcPostAPI  ==>>>'.$result.PHP_EOL;
    }
    return $result;
}


function getCdnVideo($src)
{
    global $sysConfig;
    $post['type'] = 'video';
    $post['srcUrl'] = $src;
    $post['appName'] = 'sada';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sysConfig['video']['cdn_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_HEADER,0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_TIMEOUT, 180);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        echo $httpCode.PHP_EOL;
        echo "get CDN video error set the check=2 err1".PHP_EOL;
        return false;
    }
    $rel = json_decode($result,true);
    return $rel;
}
