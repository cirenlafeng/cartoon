<?php 
//设定页面编码
header("Content-Type:text/html;charset=utf-8");
//设定时区
date_default_timezone_set('Asia/Shanghai');
//全局加载
include_once(dirname(__FILE__).'/conf/include.php');


$todayDate = date('Y-m-d H:i:s');

$hour = date('H',strtotime($todayDate));
echo $todayDate.' '.$hour.PHP_EOL;
echo date('H',strtotime('2017-10-10 10:22:10')).PHP_EOL;
echo date('H',strtotime('2017-10-10 09:22:10')).PHP_EOL;
echo date('H',strtotime('2017-10-10 07:22:10')).PHP_EOL;
die();




$url = "https://docs.google.com/uc?id=0B-gVUZkFfjADNDJ6djBXMEUxNDQ&export=download";
$pdf = file_get_contents($url);
echo strlen($pdf)."\n";die();

#=================测试足球赛事====================#

$header = [
    'Content-Type:application/x-www-form-urlencoded; charset=UTF-8',
    'X-Requested-With:XMLHttpRequest',
    'Access-token:1234567890987654',
];

$url = 'http://api.wekoora.com/ar_AE/api/get_foot_ball_score/1';
//$url = 'http://test.wekoora.com/ar_AE/api/get_foot_ball_score/1';
//$url = 'http://api.kukuvideo.com/ar_AE/api/get_foot_ball_score/1';

echo "URL :: {$url} ...".PHP_EOL;

use Ares333\CurlMulti\Core;
$stime = microtime(true);
$curl = new Core();
$curl->opt [CURLOPT_USERAGENT]	 	= getUserAgentInfo();
$curl->opt [CURLOPT_HTTPHEADER]	 	= $header;
$curl->opt [CURLOPT_REFERER]	 	= getUserReferer();
$curl->opt [CURLOPT_SSL_VERIFYPEER]	= FALSE;//不验证SSL
$curl->opt [CURLOPT_SSL_VERIFYHOST]	= FALSE;//不验证SSL
$curl->cbTask = array('work');
$curl->maxThread = 100;//线程数
$curl->maxTry = 1;//失败重试
$curl->start();
$etime = microtime(true);
echo "Finished in .. ". round($etime - $stime, 3) ." seconds\n";

function work()
{
    global $curl;
    global $url;

    //特殊设置
    for ($i = 1 ; $i <= 1000; $i++)
    {
        $curl->add([
            'url' => $url,
            'args' => $i,
        ], 'back');
    }
    $curl->cbTask = null;
}

function back($result, $args)
{
    if ($result['info']['http_code'] != 200) {
        echo "#Error HTTP CODE : http_code[ {$result['info']['http_code']} ] can not get Html urls  -->>   URL = {$result['info']['url']}  ..... " . PHP_EOL;
        return false;
    }
    $data = (array)json_decode($result['content']);
//    print_format($data);
    echo $args.' : code = '.$data['code'].' , msg = '.$data['message'].' , result.length = '.strlen($result['content']).PHP_EOL;
}




/*
var_dump(stristr("Hello world!","WORLDs"));die;

$content = 'http://www.safarin.net/2017/02/10/%D8%A8%D8%A7%D9%84%D8%B5%D9%88%D8%B1-%D8%AC%D9%88%D9%84%D8%A9-%D8%AF%D8%A7%D8%AE%D9%84-%D9%85%D9%86%D8%AA%D8%AC%D8%B9-%D9%84%D8%A7%D8%BA%D9%88%D9%86%D8%A7-%D8%A8%D9%8A%D8%AA%D8%B4-%D8%A7%D9%84/';
echo sha1($content).PHP_EOL;die();
sort($content);
print_format($content);
*/

/*
//命令行
// exec("curl -d \"title=111&tag=1\" http://faq.mysada.com/api/v1/crawler/rev", $res);
// var_dump($res);die();



$content = <<<CON
<p><img src="http://i.cdn.turner.com/dr/cnnarabic/cnnarabic/release/sites/default/files/styles/landscape_300x170/public/image/saudi-uber.jpg" /></p>
            <p>دبي، الإمارات العربية المتحدة (CNN) -- قليلة هي بلدان العالم التي شعرت بالتأثير الكبير لانخفاض أسعار النفط مثلما شعرت به المملكة العربية السعودية.</p>

<p>قد يعجبك أيضا.. </p>

<p>ورغم أن هذا الانخفاض قد أثر على الفرص الاقتصادية للكثير من السعوديين، إلّا أن هناك صناعة محلية واحدة أظهرت تقدماً ونجاحاً كبيرين وسط هذا "الكساد النفطي" الكبير، إذ يعتقد المدير الإقليمي السابق لغوغل، عبد الرحمن طرابزوني، أن أزمة النفط أثبتت أنها "نقمة تخفي نعمة" لقطاع التكنولوجيا الجديد في البلاد.</p>

<p>وتستثمر السعودية حالياً بقطاع التكنولوجيا، بمبالغ تصل إلى مليارات الدولارات، في محاولة لتنويع اقتصاد البلاد، وتقليص اعتمادها على أسعار النفط المتقلبة.</p>

<p>قد يهمك أيضا.. </p>

<p>وكان قد أكّد طرابزوني لرواد الأعمال الشباب وكبار رجال الأعمال في منتدى عقد مؤخراً في العاصمة الرياض، أن أي شخص اليوم لديه الفرصة لخلق شيء قيّم ومهم يتمكن العالم من استخدامه.</p>

<p>وبينما تسعى العديد من البلدان إلى إعادة تكرار تجربة نجاح "وادي السيليكون" على أراضيها، تحتفظ السعودية ببعض أعلى نسب النجاح للقيام بذلك، لما تتمتع به من موارد اقتصادية تساعدها على تحقيق ذلك.</p>

<p>وأيضا..</p>

<p>وقد بدأت السعودية في رحلة النجاح هذه بعد أن أعلن صندوق الثروة السيادية في السعودية عن مشاريع للاستثمار بقيمة 45 مليار دولار في صندوق تكنولوجيا شركة الاتصالات سوفت بانك اليابانية، كما قام الصندوق السعودي في يونيو/حزيران بالاستثمار بمبلغ 3.5 مليارات دولار في شركة "أوبر."</p>

<p>كما قامت المملكة أيضاَ بإنشاء حاضنات تكنولوجيا وصناديق رأس مال استثمارية، في مقرّات رئيسية في الرياض ومكاتب في مينلو بارك، بكاليفورنيا.</p>

<p>شاهد أيضا..</p>

<p>ويعتقد المستثمر التكنولوجي الأمريكي كريستوفر شرويدر أن ما يحدث في السعودية، هو تمثيل أعمق عن مسار جديد لريادة الأعمال يمر عبر منطقة الشرق الأوسط، مضيفاً أن الشركات الناشئة هذه "تحوي مفاجآت سارة، إذ تتولى النساء إدارة أكثر من 25 بالمائة منها."</p>
CON;

// $content = addslashes($content);
// echo $content."\n";

$url = 'http://faq.mysada.com/api/v1/crawler/rev';
// $url = 'http://www.00ksw.net/html/1/1385/';
// $url = 'http://localhost/webEngine/test2.php';
$header_uft8 = array('Content-Type:application/x-www-form-urlencoded;charset=UTF-8');
$post_data = array(
				'type'=>1,
				'title'=>'11111111نفط السعودية الجديد.. التكنولوجيا وريادة الأعمال!',
				'tag'=>17,
				'url'=>'http://arabic.cnn.com/business/2016/12/11/saudi-arabia-oil-bust-tech-sector#2',
				'content'=>$content
				);

$post = http_build_query($post_data);

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

curl_close($ch);

var_dump($post);
var_dump($result);
// echo $result. PHP_EOL;

*/


