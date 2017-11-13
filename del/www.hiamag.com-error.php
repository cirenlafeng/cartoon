<?php 
defined('HOST_PATH') or exit("contorl path error");

//*****配置文件在页面底部*****//

//抓取栏目所有新闻链接
// $result['info']['http_code'] 返回状态 , $result['content'] 返回页面内容
// $args = ['url' => 'url','tag' => 17,'type' => 1,'diff' => 1];
function www_hiamag_com_GetUrlList($result, $args) 
{
	//验证返回状态
    if ($result['info']['http_code'] != 200) {
        echo "#Error : http_code[ {$result['info']['http_code']} ] can not get Html urls  -->>  Tag [ {$args['tag']} ] , URL [ {$args['url']} ]  " . PHP_EOL;
        echo $result['content'].PHP_EOL;
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
	phpQuery::newDocumentHTML($result['content']);//解析html

	//选择队列区块
	$articles = pq('.post-image');
	// print_format($articles);

	//提取页面url列表，缩略图；使用phpQuery、simple_html_dom、正则表达式处理
	$sqlData = array();
	foreach ($articles as $article)
	{
		$temp = $sqlBaseData;
		//内文链接
		$url = pq($article)->find('.post-title a')->attr('href');
		if (strlen($url) > 10) {
			$url = 'http://www.hiamag.com'.$url;

			$url = str_replace(' ','+',$url);//转义url里面的空格
			$temp['url'] = $url;

			//缩略图
			$thumbnail = pq($article)->find('a img')->attr('src');
			$temp['thumbnail'] = $thumbnail;

			$sqlData[] = $temp;
		}else{
			echo "#Error : www_mubasher_info_GetUrlList";
		}
	}

	phpQuery::unloadDocuments();

	print_format($sqlData,'url list sqlData');
	//保存url信息：saveUrlList(array());
	// $dbResult = saveUrlList($sqlData);
	// print_format($dbResult);
}

//抓取新闻内容
function www_hiamag_com_GetBodyInfo($result, $args)
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
			$args = www_hiamag_com_ResolveHtml($args);

			// 保存解析结果
			saveBody($args);
		}
			break;
		#只获取了html，未完成解析
		case 1:
		{
			//解析html
			$args = www_hiamag_com_ResolveHtml($args);

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

function www_hiamag_com_ResolveHtml($data)
{
	//解析页面：使用phpQuery[默认]、simple_html_dom、正则表达式处理

    //处理内容页面
	phpQuery::newDocumentHTML($data['html']);

	//提取选定内容
	$article = pq('article');

	//清理页面内容
	$article = removeHtml($article);

	//title
    $title = $article->find('h1')->text();

    //time
    $time = $article->find('.mi-article__dates-container')->text();
    $time = strReplaceNT($time);

    //content
    $content = $article->find('.article__content-text')->html();

    //特殊处理
    $content = preg_replace('/<figure([^>]*?)>/i', '<p>', $content);//处理头图部分
    $content = preg_replace('/<\/figure>/i', '</p>', $content);

    //content正则过滤[函数内有重用过滤正则]
    $patten = array(
    		// '/<div class=\"post-featured\".*<\/div>/ism',
    	);
    $content = removeHtmlByPregs($patten, $content);

    //字符串批量过滤
    // $patten = array(' dir="RTL"',' dir="LTR"','</strong>','</span>','#');
    // $content = str_replace($patten, '', $content);

    //处理头图
    $matches = array();
    preg_match_all('/<img([^>]*?)>/i', $content, $matches);
    if (count($matches[0]) < 1) {
    	if (strlen($topImg = $data['thumbnail']) < 1) {
			$topImg = $article->find('.img-cont img')->attr('src');
    	}
    	$content = insertTopImg($topImg, $content);
    }

    //处理所有内文img标签width属性100%
    $content = resetContentImg($content);

    phpQuery::unloadDocuments();

    $data['title'] = $title;
    $data['time'] = $time;
    $data['content'] = $content;

    // unset($data['html']);
    // print_format($data,'$data');die();

	return $data;
}


//分配的当前业务负责人ID
$operatorID['www.hiamag.com'] = 0;

//urlList配置文件
$urlInfo['www.hiamag.com'] = [
	#0
	[
		'url' => 'http://www.hiamag.com/%D9%85%D8%B7%D8%A8%D8%AE/%D9%88%D8%B5%D9%81%D8%A7%D8%AA',
		'tag' => '15',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#1
	[
		'url' => 'http://www.hiamag.com/%D9%85%D8%B7%D8%A8%D8%AE/%D9%86%D8%B5%D8%A7%D8%A6%D8%AD-%D8%A7%D9%84%D9%85%D8%B7%D8%A8%D8%AE',
		'tag' => '15',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#2
	[
		'url' => 'http://www.hiamag.com/%D8%B3%D9%8A%D8%A7%D8%AD%D8%A9/%D9%85%D8%B7%D8%A7%D8%B9%D9%85',
		'tag' => '14',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#3
	[
		'url' => 'http://www.hiamag.com/%D8%B3%D9%8A%D8%A7%D8%AD%D8%A9/%D9%81%D9%86%D8%A7%D8%AF%D9%82-%D9%88-%D8%B3%D8%A8%D8%A7',
		'tag' => '14',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#4
	[
		'url' => 'http://www.hiamag.com/%D8%B3%D9%8A%D8%A7%D8%AD%D8%A9/%D8%A3%D8%AE%D8%A8%D8%A7%D8%B1-%D8%A7%D9%84%D8%B3%D9%81%D8%B1',
		'tag' => '14',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#5
	[
		'url' => 'http://www.hiamag.com/%D8%B3%D9%8A%D8%A7%D8%AD%D8%A9/%D9%86%D8%B5%D8%A7%D8%A6%D8%AD-%D8%A7%D9%84%D8%B3%D9%81%D8%B1',
		'tag' => '14',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#6
	[
		'url' => 'http://www.hiamag.com/%D8%B3%D9%8A%D8%A7%D8%AD%D8%A9/%D9%88%D8%AC%D9%87%D8%A7%D8%AA-%D8%B3%D9%8A%D8%A7%D8%AD%D9%8A%D8%A9',
		'tag' => '14',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#7
	[
		'url' => 'http://www.hiamag.com/fashionshows',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#8
	[
		'url' => 'http://www.hiamag.com/%D9%85%D9%88%D8%B6%D8%A9-%D9%88-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1/%D8%AC%D8%AF%D9%8A%D8%AF-%D8%A7%D9%84%D9%85%D9%88%D8%B6%D8%A9',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#9
	[
		'url' => 'http://www.hiamag.com/%D9%85%D9%88%D8%B6%D8%A9-%D9%88-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1/%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1-%D9%85%D8%AD%D8%AC%D8%A8%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#10
	[
		'url' => 'http://www.hiamag.com/%D9%85%D9%88%D8%B6%D8%A9-%D9%88-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1/%D8%AA%D9%8A%D8%A7%D8%B1-%D8%B9%D9%86-%D9%82%D8%B1%D8%A8',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#11
	[
		'url' => 'http://www.hiamag.com/%D9%85%D9%88%D8%B6%D8%A9-%D9%88-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1/%D9%85%D8%AD%D9%83%D9%85%D8%A9-%D8%A7%D9%84%D9%85%D9%88%D8%B6%D8%A9',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#12
	[
		'url' => 'http://www.hiamag.com/%D9%85%D9%88%D8%B6%D8%A9-%D9%88-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1/%D9%85%D8%B4%D8%A7%D9%87%D9%8A%D8%B1-%D9%88-%D9%85%D9%88%D8%B6%D8%A9',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#13
	[
		'url' => 'http://www.hiamag.com/%D9%85%D9%88%D8%B6%D8%A9-%D9%88-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1/%D8%B9%D8%A8%D8%A7%D9%8A%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#14
	[
		'url' => 'http://www.hiamag.com/%D9%85%D9%88%D8%B6%D8%A9-%D9%88-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1/%D8%A3%D9%81%D8%B6%D9%84-%D8%A7%D9%84%D8%B9%D8%A7%D8%B1%D8%B6%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#15
	[
		'url' => 'http://www.hiamag.com/%D9%85%D9%88%D8%B6%D8%A9-%D9%88-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1/%D8%A7%D8%B7%D9%84%D8%A7%D9%84%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#16
	[
		'url' => 'http://www.hiamag.com/%D9%85%D9%88%D8%B6%D8%A9-%D9%88-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1/%D8%A7%D9%83%D8%B3%D8%B3%D9%88%D8%A7%D8%B1%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#17
	[
		'url' => 'http://www.hiamag.com/%D9%85%D9%88%D8%B6%D8%A9-%D9%88-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1/%D9%85%D8%B5%D9%85%D9%85%D9%88-%EF%BA%8D%D9%84%D8%A7%EF%BA%AF%D9%8A%D8%A7%D8%A1',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#18
	[
		'url' => 'http://www.hiamag.com/%D9%85%D9%88%D8%B6%D8%A9-%D9%88-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1/%D8%AA%D8%B5%D9%88%D9%8A%D8%B1-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#19
	[
		'url' => 'http://www.hiamag.com/%D8%AC%D9%85%D8%A7%D9%84/%D9%85%D9%83%D9%8A%D8%A7%D8%AC',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#20
	[
		'url' => 'http://www.hiamag.com/%D8%AC%D9%85%D8%A7%D9%84/%D8%AA%D9%8A%D8%A7%D8%B1%D8%A7%D8%AA-%D8%AC%D8%AF%D9%8A%D8%AF%D8%A9',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#21
	[
		'url' => 'http://www.hiamag.com/%D8%AC%D9%85%D8%A7%D9%84/%D8%A7%D9%84%D8%B9%D9%86%D8%A7%D9%8A%D8%A9-%D8%A8%D8%A7%D9%84%D8%A8%D8%B4%D8%B1%D8%A9',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#22
	[
		'url' => 'http://www.hiamag.com/%D8%AC%D9%85%D8%A7%D9%84/%D8%B9%D9%85%D9%84%D9%8A%D8%A7%D8%AA-%D8%AA%D8%AC%D9%85%D9%8A%D9%84',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#23
	[
		'url' => 'http://www.hiamag.com/%D8%AC%D9%85%D8%A7%D9%84/%D8%A7%D9%84%D8%B4%D8%B9%D8%B1',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#24
	[
		'url' => 'http://www.hiamag.com/%D8%AC%D9%85%D8%A7%D9%84/%D9%86%D8%B5%D8%A7%D8%A6%D8%AD-%D8%AC%D9%85%D8%A7%D9%84%D9%8A%D8%A9',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#25
	[
		'url' => 'http://www.hiamag.com/%D8%AC%D9%85%D8%A7%D9%84/%D8%A5%D8%B7%D9%84%D8%A7%D9%84%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#26
	[
		'url' => 'http://www.hiamag.com/%D8%AC%D9%85%D8%A7%D9%84/%D8%AC%D8%AF%D9%8A%D8%AF-%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D8%AD%D8%B6%D8%B1%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#27
	[
		'url' => 'http://www.hiamag.com/%D8%AC%D9%85%D8%A7%D9%84/%D8%B9%D8%B7%D9%88%D8%B1',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#28
	[
		'url' => 'http://www.hiamag.com/%D8%AC%D9%85%D8%A7%D9%84/%D8%A3%D8%AE%D8%A8%D8%A7%D8%B1-%D8%AC%D9%85%D8%A7%D9%84',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#29
	[
		'url' => 'http://www.hiamag.com/%D8%AC%D9%85%D8%A7%D9%84/%D8%AC%D8%B1%D8%A8%D9%86%D8%A7-%D9%84%D9%83',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#30
	[
		'url' => 'http://www.hiamag.com/%D9%85%D8%AC%D9%88%D9%87%D8%B1%D8%A7%D8%AA/%D9%85%D8%AC%D9%88%D9%87%D8%B1%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#31
	[
		'url' => 'http://www.hiamag.com/%D9%85%D8%AC%D9%88%D9%87%D8%B1%D8%A7%D8%AA/%D8%B3%D8%A7%D8%B9%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#32
	[
		'url' => 'http://www.hiamag.com/%D9%85%D8%AC%D9%88%D9%87%D8%B1%D8%A7%D8%AA/%D9%85%D8%B9%D8%A7%D8%B1%D8%B6-%D9%85%D8%AC%D9%88%D9%87%D8%B1%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#33
	[
		'url' => 'http://www.hiamag.com/%D9%85%D8%AC%D9%88%D9%87%D8%B1%D8%A7%D8%AA/%D8%A3%D8%AE%D8%A8%D8%A7%D8%B1-%D8%A7%D9%84%D9%85%D8%AC%D9%88%D9%87%D8%B1%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#34
	[
		'url' => 'http://www.hiamag.com/%D8%A7%D9%84%D8%A3%D8%B9%D8%B1%D8%A7%D8%B3/%D8%A7%D9%84%D8%A7%D8%B9%D8%B1%D8%A7%D8%B3-%D8%A3%D8%B2%D9%8A%D8%A7%D8%A1',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#35
	[
		'url' => 'http://www.hiamag.com/%D8%A7%D9%84%D8%A3%D8%B9%D8%B1%D8%A7%D8%B3/%D8%A7%D9%84%D8%A3%D8%B9%D8%B1%D8%A7%D8%B3-%D8%AC%D9%85%D8%A7%D9%84',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#36
	[
		'url' => 'http://www.hiamag.com/%D8%A7%D9%84%D8%A3%D8%B9%D8%B1%D8%A7%D8%B3/%D8%A7%D9%84%D8%A3%D8%B9%D8%B1%D8%A7%D8%B3-%D9%85%D8%AC%D9%88%D9%87%D8%B1%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#37
	[
		'url' => 'http://www.hiamag.com/%D8%AF%D9%8A%D9%83%D9%88%D8%B1-%D9%88-%D9%81%D9%86%D9%88%D9%86/%D8%AF%D9%8A%D9%83%D9%88%D8%B1',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '2',
	],
	#38
	[
		'url' => 'http://www.hiamag.com/%D8%AF%D9%8A%D9%83%D9%88%D8%B1/%D8%AF%D9%8A%D9%83%D9%88%D8%B1-%D8%A7%D9%84%D9%85%D8%B7%D8%A8%D8%AE',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '2',
	],
	#39
	[
		'url' => 'http://www.hiamag.com/%D8%AF%D9%8A%D9%83%D9%88%D8%B1/%D8%AF%D9%8A%D9%83%D9%88%D8%A7%D8%B1%D8%A7%D8%AA-%D8%AF%D8%A7%D8%AE%D9%84%D9%8A%D8%A9',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '2',
	],
	#40
	[
		'url' => 'http://www.hiamag.com/%D8%AF%D9%8A%D9%83%D9%88%D8%B1/%D8%AF%D9%8A%D9%83%D9%88%D8%B1%D8%A7%D8%AA-%D8%AE%D8%A7%D8%B1%D8%AC%D9%8A%D8%A9',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '2',
	],
	#41
	[
		'url' => 'http://www.hiamag.com/%D8%A3%D9%84%D8%A8%D9%88%D9%85%D8%A7%D8%AA-%D8%B5%D9%88%D8%B1/%D9%85%D8%B4%D8%A7%D9%87%D9%8A%D8%B1',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '2',
	],
	#42
	[
		'url' => 'http://www.hiamag.com/%D8%A3%D9%84%D8%A8%D9%88%D9%85%D8%A7%D8%AA-%D8%B5%D9%88%D8%B1/%D9%85%D9%88%D8%B6%D8%A9',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '2',
	],
	#43
	[
		'url' => 'http://www.hiamag.com/%D8%A3%D9%84%D8%A8%D9%88%D9%85%D8%A7%D8%AA-%D8%B5%D9%88%D8%B1/%D8%AC%D9%85%D8%A7%D9%84',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '2',
	],
	#44
	[
		'url' => 'http://www.hiamag.com/%D8%A3%D9%84%D8%A8%D9%88%D9%85%D8%A7%D8%AA-%D8%B5%D9%88%D8%B1/%D9%85%D9%86%D9%88%D8%B9%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '2',
	],
	#45
	[
		'url' => 'http://www.hiamag.com/%D8%A3%D9%84%D8%A8%D9%88%D9%85%D8%A7%D8%AA-%D8%B5%D9%88%D8%B1/%D8%A3%D8%B9%D8%B1%D8%A7%D8%B3',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '2',
	],
	#46
	[
		'url' => 'http://www.hiamag.com/%D8%A3%D9%84%D8%A8%D9%88%D9%85%D8%A7%D8%AA-%D8%B5%D9%88%D8%B1/%D9%85%D8%AC%D9%88%D9%87%D8%B1%D8%A7%D8%AA',
		'tag' => '9',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '2',
	],
	#47
	[
		'url' => 'http://www.hiamag.com/%D9%85%D8%B4%D8%A7%D9%87%D9%8A%D8%B1/%D9%86%D8%AC%D9%88%D9%85-%D8%A8%D9%88%D9%84%D9%8A%D9%88%D8%AF',
		'tag' => '8',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#48
	[
		'url' => 'http://www.hiamag.com/%D9%85%D8%B4%D8%A7%D9%87%D9%8A%D8%B1/%D9%86%D8%AC%D9%88%D9%85-%D8%AA%D8%B1%D9%83%D9%8A%D8%A7',
		'tag' => '8',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#49
	[
		'url' => 'http://www.hiamag.com/%D9%85%D8%B4%D8%A7%D9%87%D9%8A%D8%B1/%D9%85%D8%B4%D8%A7%D9%87%D9%8A%D8%B1-%D8%A7%D9%84%D8%B9%D8%A7%D9%84%D9%85',
		'tag' => '8',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#50
	[
		'url' => 'http://www.hiamag.com/%D9%85%D8%B4%D8%A7%D9%87%D9%8A%D8%B1/%D9%86%D8%AC%D9%88%D9%85-%D8%A7%D9%84%D8%B9%D8%B1%D8%A8',
		'tag' => '8',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#51
	[
		'url' => 'http://www.hiamag.com/%D9%85%D8%AC%D9%88%D9%87%D8%B1%D8%A7%D8%AA/%D9%85%D8%B4%D8%A7%D9%87%D9%8A%D8%B1-%D9%88-%D9%85%D8%AC%D9%88%D9%87%D8%B1%D8%A7%D8%AA',
		'tag' => '8',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
	#52
	[
		'url' => 'http://www.hiamag.com/%D8%AF%D9%8A%D9%83%D9%88%D8%B1-%D9%88-%D9%81%D9%86%D9%88%D9%86/%D8%A8%D9%8A%D9%88%D8%AA-%D8%A7%D9%84%D9%85%D8%B4%D8%A7%D9%87%D9%8A%D8%B1',
		'tag' => '8',
		'type' => '1',
		'type' => '1',
		'diff' => '1',
		'weight' => '1',
	],
];


