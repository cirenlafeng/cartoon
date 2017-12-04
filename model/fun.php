<?php
defined('HOST_PATH') or exit("contorl path error");


function print_format($value='',$name='value')
{
    echo "\n<PRE>\n";
    echo "<br />===================== {$name} =================<br />\n";
    print_r($value);
    echo "<br />-----------------------------------------------<br />\n";
}

//获取域名
function getDomain($url, $http = '')
{
    $temp = parse_url($url);
    $urlAll = $http.$temp['host'];
    return $urlAll;
}

function dbo() {
    global $sysConfig;

    return DBO::create($sysConfig['db']['host'], $sysConfig['db']['user'], $sysConfig['db']['pwd'], $sysConfig['db']['name'], $sysConfig['db']['char']);
}

function getControlFunFirstName($value='')
{
    $str = str_replace('.', '_', $value);
    $str = str_replace('-', '_', $str);
    return $str;
}

function getIncludeFile($value='')
{
    //class正式文件，debug测试文件
    $filePath = CONTROLER_PATH.$value.'.class.php';
    $error = NULL;
    if(!file_exists($filePath))
    {
        $error = 'can not found file : '.$filePath;
        $filePath = CONTROLER_PATH.$value.'.debug.php';
        if(!file_exists($filePath)) {
            $error = $error.'can not found file : '.$filePath;
        }else{
            $error = NULL;
        }
    }
    if (!$error) {
        return $filePath;
    }else{
        echo $error.PHP_EOL;
        die();
    }
}

//html常规过滤
function removeHtml($obj)
{
    if (is_object($obj)) {
        $obj->find('script')->remove();
        $obj->find('iframe')->remove();
        $obj->find('frame')->remove();
        $obj->find('noscript')->remove();
        // $obj->find('a')->remove();//会去掉连接内文字
    }
    return $obj;
}

function removeHtmlByPregs($patten = array(), $content, $patten3 = null)
{
    //常见规则
    $patten2 = array(
        '/<!--.*?-->/',
        '/ width="[^"]*"| height="[^"]*"/i',
        #过滤各种标签内属性
        '/ id="[^"]*"| class="[^"]*"| alt="[^"]*"| srcset="[^"]*"| sizes="[^"]*"| style="[^"]*"| dir="[^"]*"| start="[^"]*"| itemprop="[^"]*"| align="[^"]*"| title="[^"]*"| rel="[^"]*"|/i',
        '/ id=\'[^\']*\'| class=\'[^\']*\'| alt=\'[^\']*\'| srcset=\'[^\']*\'| sizes=\'[^\']*\'| style=\'[^\']*\'| dir=\'[^\']*\'| start=\'[^\']*\'| itemprop=\'[^\']*\'| align=\'[^\']*\'| title=\'[^\']*\'| width=\'[^\']*\'| height=\'[^\']*\'|/i',
        '/ data-[^=]*="[^"]*"|/i',
        #去除各种标签
        '/<a([^>]*?)>/i',
        '/<\/a>/i',
        '/<strong([^>]*?)>/i',
        '/<\/strong>/i',
        '/<b>/i',
        '/<b ([^>]*?)>/i',
        '/<\/b>/i',
        '/<span([^>]*?)>/i',
        '/<\/span>/i',
        '/<center([^>]*?)>/i',
        '/<\/center>/i',
        '/<div([^>]*?)>/i',
        '/<\/div>/i',
        '/<figure([^>]*?)>/i',
        '/<\/figure>/i',
        '/<font([^>]*?)>/i',
        '/<\/font>/i',
        '/<abbr([^>]*?)>/i',
        '/<\/abbr>/i',
        '/<p> <\/p>/i',
        '/<p><\/p>/i',
    );
    if ($patten3) {
        $patten2 = $patten3;
    }
    foreach ($patten2 as $key => $value) {
        $patten[] = $value;
    }
    foreach ($patten as $key => $value)
    {
        $content = removeHtmlByPreg($value, $content);
    }
    $content = trim($content);
    $content = str_replace("<p>", "\n<p>", $content);
    $patten = array("\n\n<p>");
    for ($i=0; $i < 10; $i++) {
        $content = str_replace($patten, "\n<p>", $content);
    }
    return $content;
}

//针对公共方法
function removeHtmlByPregsForCommon($patten = array(), $content, $patten3 = null)
{
    //常见规则
    $patten2 = array(

    );
    if ($patten3) {
        $patten2 = $patten3;
    }
    foreach ($patten2 as $key => $value) {
        $patten[] = $value;
    }
    foreach ($patten as $key => $value)
    {
        $content = removeHtmlByPreg($value, $content);
    }
    $content = trim($content);
    $content = str_replace("<p>", "\n<p>", $content);
    $patten = array("\n\n<p>");
    for ($i=0; $i < 10; $i++) {
        $content = str_replace($patten, "\n<p>", $content);
    }
    return $content;
}

function removeHtmlByPreg($patten, $content)
{
    return $content = preg_replace($patten,'',$content);
}


//干掉换行符
function strReplaceNT($str)
{
    $patten = array("\r\n", "\n", "\r","    ");
    //先替换掉\r\n,然后是否存在\n,最后替换\r
    $str = trim(str_replace($patten, "", $str));
    for ($i=0; $i < 20; $i++) {
        $str = str_replace('  ', ' ', $str);
    }
    return $str;
}

/**
 * content特殊处理：插入缩略图当做首图等
 * @param string $topImg 缩略图
 * @param string $content 内容
 * @return string
 */
function insertTopImg($topImg='', $content ='')
{
    if (strlen($topImg) > 5 && strlen($content) > 10) {
        if ($temp = explode('?', $topImg)) {
            if (!strstr($topImg,'.php')){
                $topImg = $temp[0];
            }
        }
        if (strlen($topImg) > 10) {
            $content = "<p><img src=\"{$topImg}\" /></p>\n".$content;
        }
    }else{
        echo "#Warning : fun.insertTopImg -> topImg( ".strlen($topImg)." ) or content( ".strlen($content)." ) is NULL ....<br/>" . PHP_EOL;
    }
    return $content;
}

function resetContentImg($content='')
{
    $patten = '/<img /i';
    $content = preg_replace($patten , '<img width="100%" ', $content);
    return $content;
}



//安全json格式，可post和存db
function arrayToJsonSafe($array)
{
    $json = json_encode($array);
    return preg_replace("#\\u(([0-9a-f]+?){4})#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $json);
}
function jsonToArraySafe($json)
{
    return (array)json_decode($json, true);
}

function getPregData($pattern='', $html='', $i = 1)
{
    if (strlen($html) > 0) {
        $matches = array();
        preg_match($pattern, $html, $matches);
        if (@strlen($matches[$i]) > 0) {
            $data = $matches[$i];
            return $data;
        }else{
            echo 'can not found ['.$pattern.']'.PHP_EOL;
            return NULL;
        }
    }else{
        echo 'html is empty ! '."\n{$html}\n".PHP_EOL;
        return NULL;
    }
}
function getPregDataArray($pattern='', $html='')
{
    if (strlen($html) > 0) {
        $matches = array();
        preg_match_all($pattern, $html, $matches);
        return $matches;
    }else{
        echo 'html is empty ! '."\n{$html}\n".PHP_EOL;
        return NULL;
    }
}

function checkStringIsBase64($str) {
    return $str == base64_encode(base64_decode($str)) ? true : false;
}

function getSafeURL($url='')
{
    if (strlen($url) > 0) {
        $temURL = urlencode($url);
        $temURL = str_replace('%3A', ':', $temURL);
        $temURL = str_replace('%2F', '/', $temURL);
        $temURL = str_replace('%3F', '?', $temURL);
        $temURL = str_replace('%26', '&', $temURL);
        $temURL = str_replace('%23', '#', $temURL);
        $temURL = str_replace('%40', '@', $temURL);
        $temURL = str_replace('%24', '$', $temURL);
        $temURL = str_replace('%25', '%', $temURL);
        $temURL = str_replace('%3D', '=', $temURL);
        return $temURL;
    }else{
        return NULL;
    }
}

function getUrlHttpCodeCheck($result='')
{
    // print_format($result,'$result');die();
    if ($result['info']['http_code'] != 200) {
        echo "#Error HTTP CODE : http_code[ {$result['info']['http_code']} ] can not get Html urls  -->>   URL = {$result['info']['url']}  ..... " . PHP_EOL;
        return false;
    }
    if (strlen($result['content']) < 1) {
        echo "#Error NO HTML : html resource is null  URL [ {$result['info']['url']} ]  ....." . PHP_EOL;
        return false;
    }
    return true;
}
function getBodyHttpCodeCheck($result='')
{
    // print_format($result,'$result');die();
    if ($result['info']['http_code'] != 200) {
        echo "#Error : http_code [ {$result['info']['http_code']} ]  Html resource  -->>  URL = {$result['info']['url']}  ....." . PHP_EOL;
        return false;
    }
    return true;
}

/**
 * 将内容中图片相对地址改成绝对地址
 * @param $content
 * @param string $host
 * @return mixed
 */
function replaceContentImgsWithHost($content, $host ='')
{
    if (strlen($host) < 1) return $content;

    //提取所有img html，和最新img url
    $replaceArr = array();
    preg_match_all('#<img.*?src="([^"]*)"[^>]*>#i', $content, $match);
    for ($i = 0; $i < count($match[1]); $i++)
    {
        if (!strpos($match[1][$i],'://'))
        {
            $temp = [];
            $temp['find'] = $match[0][$i];
            $temp['replace'] = str_replace('//','/',$temp['replace']);
            $temp['replace'] = '<img src="'.$host.$match[1][$i].'" />';
            $replaceArr[] = $temp;
        }
    }

    foreach ($replaceArr as $replace)
    {
        $content = str_replace($replace['find'],$replace['replace'],$content);
    }

    return $content;
}

function alertTips($msg)
{
    $javascriptAlert = <<<ALERT
<script type="application/javascript">
    alert('$msg');
</script>
ALERT;
    echo $javascriptAlert;
}



/**获取js格式数组
 * @param array $data
 * @param string $key
 * @param string $modify
 * @param array $dateList
 * @return string
 */
function getJSFormatArray($data = array() , $key = 'date', $modify = "'", $dateList = null)
{
    if ($dateList)
    {
        $newData = [];
        foreach ($dateList as $value)
        {
            $date = $value['date'];
            $dateIsSet = false;
            foreach ($data as $k => $v)
            {
                if ($v['date'] == $date)
                {
                    $newData[] = $v;
                    $dateIsSet = true;
                }
            }
            if (!$dateIsSet)
            {
                $temp['date'] = $date;
                $temp['count'] = 0;
                $newData[] = $temp;
            }
        }
        $data = $newData;
    }

    $jsData = '';
    foreach ($data as $k => $value)
    {
        if ($k == 0)
        {
            $jsData = $jsData . "{$modify}{$value[$key]}{$modify}";
        }else{
            $jsData = $jsData . ", {$modify}{$value[$key]}{$modify}";
        }
    }
    return $jsData;
}

//获取关键字描述
function getKeyWords($data)
{
    $keywords = getPregData('/<meta.*?name="keyword.*?content="(.*?)".*?>/i',$data);
    if(empty($keywords))
    {
        $keywords = getPregData('/<meta.*?content=["|\'](.*?)["|\'].*?name=["|\']keyword.*?>/i',$data);
    }
    if(empty($keywords))
    {
        $keywords = getPregData('/<meta.*?name=["|\']keyword.*?content=["|\'](.*?)["|\'].*?>/i',$data);
    }
    $keywords = str_replace("،", ",", $keywords);
    $keywords = str_replace("'", "\'", $keywords);
    $keywords = str_replace("  ,", ",", $keywords);
    $keywords = str_replace(",  ", ",", $keywords);
    $keywords = str_replace(" ,", ",", $keywords);
    $keywords = str_replace(", ", ",", $keywords);
    if(mb_strlen($keywords)>=1000){
        $keywords = mb_substr($keywords, 0, 1000,"UTF-8");
    }
    if($keywords == 'text/html; charset=utf-8' || $keywords == 'text/html; charset=windows-1256'){
        $keywords = '';
    }
    $keywords = trim($keywords,"，");
    return trim($keywords,",");
}

//公共检测
function commonCheckEnd($data)
{
    //初始化
    $title   = $data['title'];
    $time    = $data['time'];
    $content = $data['content'];

    //常见问题：百分百需要过滤的标签：
    $patten = array(
                '/<script.*?<\/script>/ism',
                '/<ins.*?<\/ins>/ism',
                '/<iframe.*?<\/iframe>/ism',
                '/<form.*?<\/form>/ism',
                '/<style.*?>.*?<\/style>/ism',
                '/<blockquote.*?<\/blockquote>/ism',
                '/<video .*?<\/video>/ism',
                '/<u ([^>]*?)>/im',
                '/<u>/im',
                '/<\/u>/im',
                '/اقرأ أكثر./i',
                '/اقرأ أكثر/i',
                '/<p>   &nbsp;<\/p>/i',
                '/<p>  &nbsp;<\/p>/i',
                '/<p> &nbsp;<\/p>/i',
                '/<p>&nbsp;<\/p>/i',
                '/<strong>إقرأ ايضاً:<\/strong>/i',
                '/<p>إقرأ ايضاً:<\/p>/i',
                '/<p>اقرأ أيضا<\/p>/i',
                '/شاهد أيضاً:/i',   #另请参见
                '/إقرأ ايضاً:/i',
                '/اقرأ أيضا:/i',
                //'/اقرأ أيضا ../i',
                '/اقرأ أيضا../i',
                '/اقرا أيضًا:/i',  #另请阅读
                '/فيديو القصة الجديد:/i',
                '/فيديو القصة الجديد/i',
                '/<p>المصدر<\/p>/i',
                '/<p>المصدر:<\/p>/i',
                '/<p>:المصدر<\/p>/i',
                '/لمشاهدة المقال الأصلي، انقر هنا/i',
                '/لمشاهدة المقال الأصلي/i',
                '/لمشاهدة الفيديو:/i',
                '/اقرأ أيضًا:/i',
                '/المصادر:/i',  #资料来源
                '/مصدر الصورة/i', #图片源
                '/لمزيد من المعلومات، يمكن زيارة موقع الملتقى من هنا./i',
                '/●/im',
                '/⦁/im',
                '/اقرئي أيضاً:/im',
                '/اقرئي أيضاً :/im',
                '/اقرأ أيضاً:/im',
                '/اقرأ أيضاً :/im',
                '/اقرئي أيضاً:/im',
                '/اقرئي أيضاً :/im',
                '/أخيرا:/im',
                '/أخيرا :/im',
                '/أقرأ أيضا:/im',
                '/أقرأ أيضا :/im',   
                '/اقرا أيضا:/im',
                '/اقرا أيضا :/im', 
                '/�/im',
                '/شاهدوا الفيديو/i', #观看视频
                '/شاهد الفيديو\.\./i', #查看视频
                '/شاهدي الفيديو\.\./i',
                '/إقرأ أيضًا :/',
                '/إقرأ أيضًا:/',
             );
    $content = removeHtmlByPregsForCommon($patten, $content);

    //常见问题：百分百需要替换的标签：
    $content = str_replace('</strong>', '</p>', $content);
    $content = str_replace('<strong>', '<p>', $content);
    $content = str_replace('<h5>', '<p>', $content);
    $content = str_replace('</h5>', '</p>', $content);
    $content = str_replace('<h4>', '<p>', $content);
    $content = str_replace('</h4>', '</p>', $content);
    $content = str_replace('<h3>', '<p>', $content);
    $content = str_replace('</h3>', '</p>', $content);
    $content = str_replace('<h2>', '<p>', $content);
    $content = str_replace('</h2>', '</p>', $content);
    $content = str_replace('<h1>', '<p>', $content);
    $content = str_replace('</h1>', '</p>', $content);
    $content = str_replace('<hr />', '', $content);
    $content = str_replace('</aside>', '', $content);
    $content = str_replace('<aside>', '', $content);
    $content = str_replace('h2>', 'p>', $content);
    $content = str_replace('h3>', 'p>', $content);
    $content = str_replace('<br><br>', '</p><p>', $content);
    $content = str_replace('<br /><br />', '</p><p>', $content);
    $content = str_replace('<br>', '</p><p>', $content);
    $content = str_replace('<br />', '</p><p>', $content);
    $content = str_replace('<br/>', '</p><p>', $content);
    $content = str_replace('<p><p>', '<p>', $content);
    $content = str_replace('</p></p>', '</p>', $content);
    $content = str_replace('<p></p>', '', $content);
    $content = str_replace('&#8220;', '“', $content);
    $content = str_replace('&#8221;', '”', $content);
    $title = str_replace('&quot;', '"', $title);
    $title = str_replace('&apos;', "\'", $title);
    $title = str_replace('&#39;', "\'", $title);
    $title = str_replace("'", "\'", $title);
    $title = strReplaceNT($title); //干掉换行符，单双引号

    //常见问题：检查是否加入审核文章:
    $keyWord = array(
                'شارك برأيك',
                'أقرأ  أيضاً',
                '<p>إقرء</p>',
                '<p>إقرأ</p>',
                'وقعت الواقعة',
                '<p>وقعت</p>',
                'شاهد الفيديو',
                'شاهدي الفيديو وحاولي تطبيقه',
                'شاهدي الفيديو وحاولي',
                'إقرأ المزيد',
                'التغطيات السابقة للمشروع',
                'بالفيديو',
              );
    foreach ($keyWord as $value) {
        if ($pos = strpos($content,$value)) 
        {
            $data['check'] = 2;
            echo "#Waring: find the article keyWord , check = 2 !".PHP_EOL;
        }
        
    }
    //标题有视频等关键字
    $keyWord2 = array(
                'فيديو',
                'شاهد',
                'بالفيديو',
                'الفيديو',
                'الفيديوهات',
                'شاهدي',
                'مشاهدة',
                'فيديوهات',
              );
    foreach ($keyWord2 as $value) {
        if ($pos = strstr($title,$value)) 
        {
            $data['check'] = 2;
            echo "#Waring: find the title keyWord , check = 2 !".PHP_EOL;
        }
        
    }
    if(mb_strlen($content)<=350)
    {
        $data['check'] = 2;
        echo "#Waring: find the article mb_strlen <= 350 , check = 2 !".PHP_EOL;
    }
    $content = str_replace('<p> </p>', '', $content); //空换行较多
    //结尾
    $data['title'] = $title;
    $data['time'] = $time;
    $data['content'] = $content;
    return $data;
}