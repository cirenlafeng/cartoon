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
*  2、operatorID     负责人ID，比如 0 代表 付东方
*/

//记得改名
function www_arriyadiyah_com_Funtion($result, $args)
{
	global $operatorID;
	$args['operatorID'] = $operatorID[$args['domain']];

	$CLASS = new www_arriyadiyah_com();//记得改名

	// $result['info']['http_code'] 返回状态 , $result['content'] 返回页面内容
	// $args = ['url' => 'url','tag' => 17,'type' => 1,'diff' => 1,'operatorID'=>0];
	switch ($args['fun'])
	{
		case 'getUrl':
			if ($result['info']['http_code'] != 200) {
		        echo "#Error : http_code[ {$result['info']['http_code']} ] can not get Html urls  -->>  Tag [ {$args['tag']} ] , URL [ {$args['url']} ]  " . PHP_EOL;
		        // print_format($result,'$result');
		        return false;
		    }
			$CLASS->getUrlList($result, $args);
			break;
		case 'getBody':
			//验证返回状态
		    if ($result['info']['http_code'] != 200) {
		    	echo "#Error : http_code [ {$result['info']['http_code']} ]  Html resource  -->>  Tag [ {$args['tag']} ] , URL [ {$args['url']} ]" . PHP_EOL;
		    	updateArticleHttpErrorCode($args['urlID'], $result['info']['http_code']);
		    	// print_format($result,'$result');
		        return false;
		    }
			$CLASS->getBodyInfo($result, $args);
			break;
		default:
			break;
	}
}

/**
* 记得改名
*/
class www_arriyadiyah_com
{
	//抓取栏目所有新闻链接
	
	public function getUrlList($result, $args)
	{
		//基础数据
	    $domain = $args['domain'];
		$sqlBaseData = array(
						'tag'=>$args['tag'],
						'type'=>$args['type'],
						'domain'=>$domain,
						'url'=>'',
						'thumbnail'=>'',
						'operatorID'=>$args['operatorID']
			);

		//处理内容页面：自己选择处理html和xml方法  newDocumentHTML  newDocumentXML[xml很多没有缩略图]
		phpQuery::newDocumentHTML($result['content']);//解析html

		//选择队列区块
		$articles = pq('article');
		// print_format($articles);

		//提取页面url列表，缩略图；使用phpQuery、simple_html_dom、正则表达式处理
		$sqlData = array();
		foreach ($articles as $article)
		{
			$temp = $sqlBaseData;
			//内文链接
			$url = pq($article)->find('h2 a')->attr('href');
			if (strlen($url) > 10)
			{
				// $url = 'http://yasater.d1g.com'.$url
				// $url = str_replace(' ','+',$url);//转义url里面的空格

				$temp['url'] = $url;

				//缩略图
				$thumbnail = pq($article)->find('.post-thumbnail img')->attr('src');
				$temp['thumbnail'] = $thumbnail;

				$sqlData[] = $temp;
			}
		}

		phpQuery::unloadDocuments();

		// print_format($sqlData,'$sqlData');return;

		//保存url信息
		$dbResult = saveUrlList($sqlData);
		// print_format($dbResult);
	}

	//这个文件基本不用动
	public function getBodyInfo($result, $args)
	{
		$returnData = array('code'=>0,'msg'=>'success!');

		//验证页面信息状态
		$status = $args['status'];
		$args['html'] = $result['content'];

		switch ($status)
		{
			#仅有目标url
			case 0:
			{
				//保存html
				if (!saveHtml($args)) return false;

				// 解析html
				$args = $this->ResolveHtml($args);

				// 保存解析结果
				saveBody($args);
			}
				break;
			#只获取了html，未完成解析
			case 1:
			{
				//解析html
				$args = $this->ResolveHtml($args);

				//保存解析结果
				saveBody($args);
			}
				break;
			#status = 2 解析成功，3 发送成功。都不处理；
			case 2:
			case 3:
				echo "#Warning : arabic_cnn_com_GetBodyInfo  -->>  urlID[{$args['urlID']}] is done ....<br/>" . PHP_EOL;
				break;
			default:
				echo "#Error : arabic_cnn_com_GetBodyInfo  -->>  no this status ['.{$status}.'] ...<br/>" . PHP_EOL;
				break;
		}

		return true;
	}

	//处理具体正文内容title，time，content解析的方法
	public function ResolveHtml($data)
	{
		#解析页面：可以使用phpQuery[使用jQuery选择期规则]、正则表达式处理[特殊处理]

		return $data;
	}
}

//分配的当前业务负责人ID
$operatorID['www.arriyadiyah.com'] = 0;

//urlList配置文件
$urlInfo['www.arriyadiyah.com'] = [
	#0
	[
		'url' => 'http://www.arriyadiyah.com/category/%D8%A7%D9%84%D8%AA%D9%86%D8%B5%D9%8A%D9%81-%D8%A7%D9%84%D8%B1%D8%A6%D9%8A%D8%B3%D9%8A-%D8%A7%D9%84%D8%AC%D8%AF%D9%8A%D8%AF/%D8%B1%D9%8A%D8%A7%D8%B6%D8%A9-%D9%85%D8%AD%D9%84%D9%8A%D8%A9',
		'tag' => '17',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
];


