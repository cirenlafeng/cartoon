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
		    }else{
		    	updateArticleHttpErrorCode($args['urlID'], $result['info']['http_code']);
		    }
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
						'tag'=>$args['tag'],
						'type'=>$args['type'],
                        'check' => $args['check'],
						'domain'=>$domain,
						'url'=>'',
						'thumbnail'=>'',
						'operatorID'=>$args['operatorID'],
						'keywords'=>$args['keywords'],
			);

		//处理内容页面：自己选择处理html和xml方法  newDocumentHTML  newDocumentXML[xml很多没有缩略图]
		phpQuery::newDocumentHTML($result['content']);//解析html

		$chapter = (int) pq('a.chapter')->text();
		if(empty($chapter))
		{
			$chapter = 0;
		}
		if($chapter == 0)
		{
			return false;
		}else{
			global $dbo;
			$exist = $dbo->loadObject("SELECT 1 FROM `articles` WHERE `check` = '{$args['check']}' AND `tag`= '{$chapter}' LIMIT 1");
			if ($exist) {
		        echo "#continue : articlesDB.saveUrl-->>BookId= {$args['check']} , Chapter= {$chapter} is existed ....<br/>" . PHP_EOL;
		        @$statisticsInfo['saveUrl']['#Warning']++;
		        return false;
		    }
		}
		$sqlBaseData['tag'] = $chapter; //章节;
		$pageCount = pq('div#morepages > a:last')->text();//总页数

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
				$temp['type'] = pq($article)->find('span')->text();
				if(empty($temp['type']))
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
				if(substr(strrchr($url, '/'),1,3) == '00.')
				{
					continue;
				}
				$temp['url'] = $temp['thumbnail'] = $url;
				$sqlData[] = $temp;
			}
		}

		phpQuery::unloadDocuments();

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
		$status = $args['status'];
		$args['html'] = $result['content'];
		//验证保存html数据是否损坏
		if (strlen($args['html']) > 0 && checkStringIsBase64($args['html'])) {
			echo "#ERROR : base64 .........{$args['urlID']}..........{$args['domain']}.........".PHP_EOL;
			//改回状态重新获取
			updateArticleStatus($args['urlID'], 0);
			return false;
		}
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
		if(!empty($data['content']) && !empty($data['title']))
		{
			echo "#continue : articlesDB.saveBody-->>BookId= {$data['check']} , Page : {$data['type']} is existed ....<br/>" . PHP_EOL;
			return false;
		}
		$html = $data['html'];
		$postData = [
	        'appName'=>'sada',
	        'type'=>'file',
	        'w'=>550,
	        'h'=>900,
	        'fileContent'=>base64_encode($html),
	        'fileName'=>md5($data['check']).'_'.$data['tag'].'_'.$data['type'].'_'.substr(strrchr($data['url'], '/'),1),
    	];
	    //下载内容图片
        $temp = (array)json_decode(srcPostAPI($postData));
	    if (!empty($temp['content'])) {
	        $data['title'] = $temp['content'];
		    $data['time'] = time();
		    $data['content'] = $temp['content'];
		    unset($data['html']);
		    $data['html'] = '';
			return $data;
	    }else{
	        echo "#Error : get cdn error  --->  BookId : {$data['tag']} Page : {$data['type']}".PHP_EOL;
	        return false;
	    }
	    
	}
}



