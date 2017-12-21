<?php 
defined('HOST_PATH') or exit("contorl path error");

/*
*  改名规则
*  1、example 改为 域名，域名中的 ”.“ ”-“ 符号都替换成 ”_“
*  2、每个php文件需要修改这个方法名和下面的类名
*  3、搜索  记得改名  查找改名处
*
*  配置文件在页面底部，包括：
*  1、urlInfo        所有任务url数据
*  2、operatorID     负责人ID
*/

//记得改名
function www_manga_ae_Funtion($result, $args)
{
	global $operatorID;
	$args['operatorID'] = $operatorID[$args['domain']];

	$CLASS = new www_manga_ae();//记得改名
	// $result['info']['http_code'] 返回状态 , $result['content'] 返回页面内容
	// $args = ['url' => 'url','tag' => 17,'type' => 1,'diff' => 1,'operatorID'=>0];
	switch ($args['fun'])
	{
		case 'getUrl':
			//验证返回状态
		    if (getUrlHttpCodeCheck($result)) {
		    	$CLASS->getUrlList($result, $args);
		    }
			break;
		case 'getBody':
			//验证返回状态
		    if (getBodyHttpCodeCheck($result)) {
		    	$CLASS->getBodyInfo($result, $args);
		    }
		    // else{
		    // 	updateArticleHttpErrorCode($args['urlID'], $result['info']['http_code']);
		    // }
			break;
		default:
			break;
	}
}

/**
* 记得改名
*/
class www_manga_ae
{
	//抓取栏目所有新闻链接
	
	public function getUrlList($result, $args)
	{
		//基础数据
	    $domain = $args['domain'];
		$sqlBaseData = array(
						'list_id' => $args['list_id'],
						'thumbnail'=>'',
						'chapter_name'=>$args['chapter_name'],
			);

		//处理内容页面：自己选择处理html和xml方法  newDocumentHTML  newDocumentXML[xml很多没有缩略图]
		phpQuery::newDocumentHTML($result['content']);//解析html

		$chapter = (float) pq('a.chapter')->text();
		if(empty($chapter))
		{
			$chapter = 0;
		}
		if($chapter == 0)
		{
			return false;
		}else{
			global $dbo;
			$exist = $dbo->loadObject("SELECT 1 FROM `comics_chapters` WHERE `list_id` = '{$args['list_id']}' AND `chapter`= '{$chapter}' LIMIT 1");
			if ($exist) {
		        echo "#continue : articlesDB.saveUrl-->>BookId= {$args['list_id']} , Chapter= {$chapter} is existed ....<br/>" . PHP_EOL;
		        @$statisticsInfo['saveUrl']['#Warning']++;
		        return false;
		    }
		}
		$sqlBaseData['chapter'] = $chapter; //章节;
		$pageFirst = pq('div#morepages > a:first')->text();//第一页
		$pageEnd = pq('div#morepages > a:last')->text();//末页

		$pageCount = $pageEnd - $pageFirst + 1;
		//获取子元素个数
		$pageCount = pq('div#morepages')->children('span')->length();
		//选择队列区块
		$articles = pq('#showchaptercontainer');
		// print_format($articles);

		//提取页面url列表，缩略图；使用phpQuery、simple_html_dom、正则表达式处理
		$sqlData = array();
		foreach ($articles as $article)
		{
			$temp = $sqlBaseData;
			//内文链接
			$url = pq($article)->find('img')->attr('src');
			if (strlen($url) > 10)
			{
				$temp['page'] = pq($article)->find('span')->text();
				if(empty($temp['page']))
				{
					continue;
				}
				if((substr(strrchr($url, '.'),1) != 'jpg') && (substr(strrchr($url, '.'),1) != 'png') && (substr(strrchr($url, '.'),1) != 'gif') && (substr(strrchr($url, '.'),1) != 'JPG') && (substr(strrchr($url, '.'),1) != 'PNG') && (substr(strrchr($url, '.'),1) != 'GIF') )
				{
					if(substr(strrchr($url, '.'),1) == 'db')
					{
						$pageCount = $pageCount - 1;
						continue;
					}
					
				}
				// if(substr(strrchr($url, '/'),1,3) == '00.')
				// {
				// 	continue;
				// }
				$temp['thumbnail'] = $url;
				$temp['domain'] = 'www.manga.ae';
				
				
				$sqlData[] = $temp;
			}else{
				$pageCount -=1;
			}
			$temp['pagecount'] = $pageCount;
		}

		phpQuery::unloadDocuments();
		//print_r($sqlData);exit();
		//print_format($sqlData,'$sqlData');return;
		//保存url信息
		if (count($sqlData) != $pageCount) {
			echo "#ERROR !!! for pageCount  . {$args['url']}  .... error !".PHP_EOL;
			return false;
		}elseif((count($sqlData) == $pageCount) && !empty($pageCount) && $pageCount > 0 ){
			saveUrlList($sqlData, $args);
		}else{
			echo "#ERROR !!! for pageCount  . {$args['url']}  .... error !".PHP_EOL;
			return false;
		}
	}

	//这个文件基本不用动
	public function getBodyInfo($result, $args)
	{
		$returnData = array('code'=>0,'msg'=>'success!');
		//验证页面信息状态
		$args['html'] = $result['content'];
		$status = $args['status'];
		switch ($status)
		{
			#仅有目标url
			case 0:
				// 解析html
				$args = $this->ResolveHtml($args);
				// 保存解析结果
				saveBody($args);
				break;
			#status = 2 解析成功，3 发送成功。都不处理；
			case 2:
			case 3:
				echo "#Warning :  {$args['domain']}::getBodyInfo  -->>  urlID[{$args['urlID']}] is done ....<br/>" . PHP_EOL;
				break;
			default:
				echo "#Error :  {$args['domain']}::getBodyInfo  -->>  no this status ['.{$status}.']   urlID = {$args['urlID']}  ...<br/>" . PHP_EOL;
				break;
		}
		return true;
	}

	//处理具体正文内容title，time，content解析的方法html(),htmlOuter(),text()
	public function ResolveHtml($data)
	{
		if(!empty($data['pic']))
		{
			echo "#continue : articlesDB.saveBody-->>BookId= {$data['list_id']} , Page : {$data['page']} is existed ....<br/>" . PHP_EOL;
			return false;
		}
		$t = time(); 
		$html = $data['html'];
		//图片高度超过1000的不裁剪原图生成
		$imgs = getimagesizefromstring($html);
		if($imgs[1] > 10000){
			$w = (int) $imgs[0];
			$h = (int) $imgs[1];
		}else{
			$w=610;
			$h=1000;
		}
		$postData = [
    'appName'=>'sada',
    'type'=>'file',
    'w'=>$w,
    'h'=>$h,
    'fileContent'=>base64_encode($html),
    'fileName'=>$t.md5($data['list_id'].'_'.$data['chapter']).$data['list_id'].'_'.$data['page'].'_'.substr(strrchr($data['thumbnail'], '/'),1),
    	];
    	
	    //下载内容图片
        $temp = (array)json_decode(srcPostAPI($postData));
	    if (!empty($temp['content'])) {
	        $data['pic'] = $temp['content'];
		    $data['time'] = time();
		    //获取图片宽高  610  1000
			$imginfo = getimagesize($temp['content']);
			if(!$imginfo[0]) $imginfo[0]=610;
			if(!$imginfo[1]) $imginfo[1]=1000;
		    $data['width'] = (int) $imginfo[0];
			$data['height'] = (int) $imginfo[1];
		    unset($data['html']);
			return $data;
	    }else{
	        echo "#Error : get cdn error  --->  BookId : {$data['list_id']} Tag : {$data['chapter']} Page : {$data['page']}".PHP_EOL;
	        return false;
	    }
	    
	}
}



