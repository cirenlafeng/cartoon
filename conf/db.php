<?php

/**
 * 各种配置项
 */
//频道编号
    $cateInfo = [
    1 => '中东',
    2 => '国际',
    3 => '经济',
    4 => '足球',
    5 => '埃及',
    6 => 'IT',
    7 => '娱乐',
    8 => '明星',
    9 => '女神',
    10 => 'CARS',
    11 => '科学',
    12 => '游戏',
    13 => '阿联酋',
    14 => '旅游',
    15 => '食物',
    16 => '健康',
    17 => '沙特',
];

$sysConfig['redis'] = '127.0.0.1';

$sysConfig['db']['host'] = '127.0.0.1';
$sysConfig['db']['user'] = 'root';
$sysConfig['db']['pwd'] = 'abcd.1234';
$sysConfig['db']['name'] = 'cartoon';
$sysConfig['db']['char'] = 'utf8';

//$sysConfig['api']['push_articles'] = 'http://faq.mysada.com/api/v1/crawler/rev';
//$sysConfig['api']['push_articles'] = 'http://faq.mysada.com/api/v1/crawler/test';
$sysConfig['api']['push_articles'] = 'http://g2c.mysada.com/?c=api&appName=sada&key=KLJASD9WR98H9FF98HSF230SFAW';
//$sysConfig['api']['push_articles'] = 'http://g2c.mysada.com/?c=api&appName=sada_test&key=KLJASD9WR98H9FF98HSF230SFAW';


$sysConfig['api']['sms_mail'] = 'http://reportmail.mysada.com/email_sms_api.php';

//站点cookie设置
$setCookie['www.alarabiya.net'] = 'YPF8827340282Jdskjhfiw_928937459182JAX666=13.56.108.107';
$setCookie['www.gheir.com'] = 'ASP.NET_SessionId=bn0x0z45yaaj00m5u1e4ea45';

$sysConfig['admin']['user'] = 'admin';
$sysConfig['admin']['pwd'] = 'admin';
//视频CDN
$sysConfig['video']['cdn_url'] = 'http://pushcdn.mysada.com/app_delay.php?key=58307deb71dd486ef8afc742056780c0';
//图片CDN
$sysConfig['RRC_GATHER_URL'] = 'http://pushcdn.mysada.com/api.php?key=58307deb71dd486ef8afc742056780c0';
//print_r($sysConfig);