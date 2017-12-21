<?php

/**
 * 各种配置项
 */


$sysConfig['redis'] = '127.0.0.1';

$sysConfig['db']['host'] = '127.0.0.1';
$sysConfig['db']['user'] = 'root';
$sysConfig['db']['pwd'] = 'abcd.1234';
$sysConfig['db']['name'] = 'comics';
$sysConfig['db']['char'] = 'utf8';


//测试服
// $sysConfig['api']['url'] = 'http://comicstest.mobibookapp.com/api/cartoon/set_temp_xsda486_4asdfg_5de_8r7w8s_df45s';
// $sysConfig['api']['key'] = '47b84030d53d2f94339e43c552062ff9';

//正式服
$sysConfig['api']['url'] = 'http://comics.mobibookapp.com/api/cartoon/set_temp_xsda486_4asdfg_5de_8r7w8s_df45s';
$sysConfig['api']['key'] = '47b84030d53d2f94339e43c552062ff9';

//图片cdn
$sysConfig['RRC_GATHER_URL'] = 'http://pushcdn.mysada.com/api.php?key=58307deb71dd486ef8afc742056780c0';