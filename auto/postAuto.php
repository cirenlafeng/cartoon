<?php 
set_time_limit(3600);
//设定页面编码
header("Content-Type:text/html;charset=utf-8");
//设定时区
date_default_timezone_set('Asia/Shanghai');

error_reporting(E_ALL ^ E_NOTICE);

//全局加载
include_once(dirname(__FILE__).'/../conf/include.php');

//配置变量加载
include_once(dirname(__FILE__).'/../conf/incloudeURL.php');

echo "<pre>".date('Y-m-d H:i:s').'<br/>'.PHP_EOL;
$stime = microtime(true);

foreach ($urlInfo['www.manga.ae'] as $key => $cartoon) {
	// $sql = "SELECT * FROM `articles` WHERE `check` = '{$cartoon['check']}' AND `status` = 2 ORDER BY tag ASC , type ASC";
	$sql = "SELECT  count(1) AS `count`,`tag`,`check`,`keywords` FROM `articles` WHERE `check` = '{$cartoon['check']}' GROUP BY tag ORDER BY tag ";
	$cartoonList = $dbo->loadAssocList($sql);
	print_r($cartoonList)die();
	foreach ($cartoonList as $k => $v) {
		$tagSql = "SELECT * FROM `articles` WHERE `check` = '{$cartoon['check']}' AND `status` = 2 AND `tag` = '{$v['tag']}' ORDER BY type ASC";
		$tagList = $dbo->loadAssocList($tagSql);
		if(count($tagList) != $v['count'])
		{
			echo "#ERROR : BookId:{$v['check']} 章节数据不完整或已导入：".$v['tag'].' 已跳过'.PHP_EOL;
			continue;
		}else{
			$url = "http://admin.mobibookapp.com/api/cartoon/set_temp_xsda486_4asdfg_5de_8r7w8s_df45s";
			$post_data['data'] 		= json_encode($tagList);
			$post_data['bookId'] 	= $cartoon['check'];
			$post_data['key']		= 'd5aafc3f489da27f4582d7a2ad76764069247_99999A';
			$post_data['chapterId']	= $v['tag'];
			$post_data['chapterName']  = $v['keywords'];
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			$output = curl_exec($ch);
			curl_close($ch);
			$result = json_decode($output,true);
			if(isset($result['status']))
			{
				if($result['status'] == 200)
				{
					$updateSql = "UPDATE `articles` SET `status` = 8 WHERE `check` = '{$cartoon['check']}' AND `tag` = '{$v['tag']}' ";
					$dbo->exec($updateSql);
					echo "#success : {$cartoon['check']} --> {$v['tag']} 导入成功!".PHP_EOL;
					continue;
				}
			}
			echo "#ERROR : BookId:{$v['check']} 数据导入失败：".$v['tag'].' 请重试'.$output.PHP_EOL;
			continue;
		}
	}
}

$etime = microtime(true);
echo "Finished in .. ". round($etime - $stime, 3) ." seconds\n";
// print_format($urlInfo, '$urlInfo');