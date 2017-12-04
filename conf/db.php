<?php

/**
 * 各种配置项
 */


$sysConfig['redis'] = '127.0.0.1';

$sysConfig['db']['host'] = '127.0.0.1';
$sysConfig['db']['user'] = 'root';
$sysConfig['db']['pwd'] = 'root';
$sysConfig['db']['name'] = 'comics';
$sysConfig['db']['char'] = 'utf8';


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