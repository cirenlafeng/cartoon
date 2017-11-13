<?php 
defined('HOST_PATH') or exit("contorl path error");

//*****配置文件在页面底部*****//

//抓取栏目所有新闻链接
// $result['info']['http_code'] 返回状态 , $result['content'] 返回页面内容
// $args = ['url' => 'url','tag' => 17,'type' => 1,'diff' => 1];
function arabic_cnn_com_GetUrlList($result, $args) 
{
	//验证返回状态
    if ($result['info']['http_code'] != 200) {
        echo "#Error : http_code[ {$result['info']['http_code']} ] can not get Html urls  -->>  Tag [ {$args['tag']} ] , URL [ {$args['url']} ]  " . PHP_EOL;
        return false;
    }
    // print_format($args,'$args');

    //基础数据
    global $operatorID;
    $domain = $args['domain'];
	$sqlBaseData = array(
					'tag'=>$args['tag'],
					'type'=>$args['type'],
					'domain'=>$domain,
					'url'=>'',
					'thumbnail'=>'',
					'operatorID'=>$operatorID[$domain]
		);

	// print_format($result,'$result');die();

	//处理内容页面
	// phpQuery::newDocumentHTML($result['content']);//解析html
	phpQuery::newDocumentXML($result['content']);//解析xml
	$articles = pq('item');
	// print_format($articles);

	//提取页面url列表，缩略图；使用phpQuery、simple_html_dom、正则表达式处理
	$sqlData = array();
	foreach ($articles as $article)
	{
		$temp = $sqlBaseData;
		//内文链接
		$url = pq($article)->find('link')->text();
		$url = str_replace(' ','+',$url);//转义url里面的空格
		$temp['url'] = $url;

		//缩略图
		// $thumbnail = pq($article)->find('link')->text();//xml没有缩略图
		// $temp['thumbnail'] = $thumbnail;

		$sqlData[] = $temp;
	}

	phpQuery::unloadDocuments();

	//保存url信息：
	$dbResult = saveUrlList($sqlData);
	// print_format($dbResult);
}

//抓取新闻内容
function arabic_cnn_com_GetBodyInfo($result, $args)
{
	//验证返回状态
    if ($result['info']['http_code'] != 200) {
    	echo "#Error : http_code [ {$result['info']['http_code']} ]  Html resource  -->>  Tag [ {$args['tag']} ] , URL [ {$args['url']} ]" . PHP_EOL;
    	updateArticleHttpErrorCode($args['urlID'], $result['info']['http_code']);
        return false;
    }

    // print_format($args,'$args');return;

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
			$args = arrabic_cnn_com_ResolveHtml($args);

			// 保存解析结果
			saveBody($args);
		}
			break;
		#只获取了html，未完成解析
		case 1:
		{
			//解析html
			$args = arrabic_cnn_com_ResolveHtml($args);

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
}

function arrabic_cnn_com_ResolveHtml($data)
{
	//解析页面：使用phpQuery[默认]、simple_html_dom、正则表达式处理
    
    //处理内容页面
	phpQuery::newDocumentHTML($data['html']);

	//提取选定内容
	$article = pq('article.main-article');

	//清理页面内容
	$article = removeHtml($article);

    //title
    $title = $article->find('h1.news-headline-desktop')->text();

    //time
    $time = $article->find('span.news-date')->text();
    $time = strReplaceNT($time);

    //content
    $content = $article->find('.article-content > .article-left')->html();

    //content正则过滤<div class="video-container">
    $patten = array(
    		'/<div class=\"news-tags clearfix\">.*<\/div>/ism',
    		'/<div class=\"video-container\">.*<\/div>/ism',
    		'/<blockquote.*?<\/blockquote>/ism',
    		'/<div class="story-inline-video".*?<\/div>/ism',
    	);
    $content = removeHtmlByPregs($patten, $content);


    //字符串批量过滤
    // $patten = array(' dir="RTL"',' dir="LTR"','<strong>','</strong>','<span>','</span>','#');
    // $content = str_replace($patten, '', $content);


    //大图 这里data-maxwidth3840px属性笔src图片地址图片质量更好
    $content = insertTopImg($topImg, $content);
    $matches = array();
    preg_match_all('/<img([^>]*?)>/i', $content, $matches);
    if (count($matches[0]) < 1) {
    	if (strlen($topImg = $data['thumbnail']) < 1) {
			$topImg = 'http:'.$article->find('.article-img > .breakpoint > img')->attr('src');
    	}
    	$content = insertTopImg($topImg, $content);
    }

    //处理所有内文img标签
    $content = resetContentImg($content);

    phpQuery::unloadDocuments();

    $data['title'] = $title;
    $data['time'] = $time;
    $data['content'] = $content;

    // unset($data['html']);
    // print_format($data);

	return $data;
}

//分配的当前业务负责人ID
$operatorID['arabic.cnn.com'] = 0;

//urlList配置文件
$urlInfo['arabic.cnn.com'] = [
	#0
	[
		'url' => 'http://arabic.cnn.com/%D8%A7%D9%84%D9%85%D9%85%D9%84%D9%83%D8%A9-%D8%A7%D9%84%D8%B9%D8%B1%D8%A8%D9%8A%D8%A9-%D8%A7%D9%84%D8%B3%D8%B9%D9%88%D8%AF%D9%8A%D8%A9/rss',
		#'http://arabic.cnn.com/%D8%A7%D9%84%D9%85%D9%85%D9%84%D9%83%D8%A9-%D8%A7%D9%84%D8%B9%D8%B1%D8%A8%D9%8A%D8%A9-%D8%A7%D9%84%D8%B3%D8%B9%D9%88%D8%AF%D9%8A%D8%A9',
		'tag' => 17,
		'type' => 1,
		'diff' => 1,
	],
	#1
	[
		'url' => 'http://arabic.cnn.com/%D9%85%D8%B5%D8%B1/rss',
		#'http://arabic.cnn.com/%D9%85%D8%B5%D8%B1',
		'tag' => 5,
		'type' => 1,
		'diff' => 1,
	],
];


