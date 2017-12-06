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
$dateTime3 = date('Y-m-d H:i:s',(time() - (86400*3)));

/**
*	漫画推送
*	$post      推送数组；
*/


$sql = "SELECT * FROM `comics_list`";
$cartoonList = $dbo->loadAssocList($sql);
foreach($cartoonList as $key=>$val){
	
	$list_id = (int) $val['id'];
	$sql = "SELECT count(1) as count,chapter,pagecount FROM `comics_chapters` WHERE `list_id`=".$list_id." AND status=2 GROUP BY `chapter`,`pagecount`";
	$row = $dbo->loadAssocList($sql);
	//按每本每章节推送
	foreach($row as $k=>$v){
		$post = [];
		$post['cartoonInfo'] = $val;
		if($v['count'] == $v['pagecount']){
			$sql1 = "SELECT `chapter_name`,`chapter`,`page`,`pagecount`,`width`,`height`,`pic` FROM `comics_chapters` WHERE `list_id`=".$list_id." AND `status`=2 AND `chapter`=".$v['chapter'];
			$row1 = $dbo->loadAssocList($sql1);

			foreach ($row1 as $num=>$value) {
				if($num == 0){
					$post['detail']['source']['chapter_name'] = $value['chapter_name'];
					$post['detail']['source']['chapter'] = $value['chapter'];
				}
				$temp = [];
				$temp['page'] = (int) $value['page'];
				$temp['pic'] = $value['pic'];
				$temp['width'] = $value['width'];
				$temp['height'] = $value['height'];
				$post['detail']['content'][] = $temp;
				
			}
			pushApi($post,$list_id,$v['chapter']);
			
		}else{
			echo "#Notice 当前章节不完整 list_id={$list_id} chapter={$v['chapter']}".PHP_EOL;
			continue;
		}

	}
	
}
function pushApi($post,$list_id,$chapter)
{

	var_dump($post);
	echo $list_id.'--->'.$chapter;die;
	$url = "http://admin.mobibookapp.com/api/cartoon/set_temp_xsda486_4asdfg_5de_8r7w8s_df45s";
	$post_data['data'] 		= json_encode($post);
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
			$time = time();
			$updateSql = "UPDATE `comics_chapters` SET `status` = 8  WHERE `list_id` = '{$list_id}' AND `chapter` = '{$chapter}'";
			$dbo->exec($updateSql);
			echo "#success : list_id={$list_id} --> chapter={$chapter} 导入成功!".PHP_EOL;
		}
	}
}
