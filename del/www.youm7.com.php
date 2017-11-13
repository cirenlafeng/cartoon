<?php 
defined('HOST_PATH') or exit("contorl path error");

//*****配置文件在页面底部*****//

//抓取栏目所有新闻链接
// $result['info']['http_code'] 返回状态 , $result['content'] 返回页面内容
// $args = ['url' => 'url','tag' => 17,'type' => 1,'diff' => 1];
function www_youm7_com_GetUrlList($result, $args) 
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

	// print_format($result,'$result');return;

	//处理内容页面
	// phpQuery::newDocumentHTML($result['content']);//解析html
	phpQuery::newDocumentHTML($result['content']);//解析xml

	//选择队列区块
	$articles = pq('#paging [class="col-xs-12 bigOneSec"]');
	// print_format($articles);

	//提取页面url列表，缩略图；使用phpQuery、simple_html_dom、正则表达式处理
	$sqlData = array();
	foreach ($articles as $article)
	{
		$temp = $sqlBaseData;
		//内文链接
		$url = 'http://www.youm7.com'.pq($article)->find('h3 a')->attr('href');
		$temp['url'] = $url;

		//缩略图
		$thumbnail = pq($article)->find('a.bigOneImg img')->attr('src');
		$temp['thumbnail'] = $thumbnail;

		$sqlData[] = $temp;
	}

	phpQuery::unloadDocuments();

	//保存url信息：saveUrlList(array());
	$dbResult = saveUrlList($sqlData);
	// print_format($dbResult);
}

//抓取新闻内容
function www_youm7_com_GetBodyInfo($result, $args)
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
			$args = www_youm7_com_ResolveHtml($args);

			// 保存解析结果
			saveBody($args);
		}
			break;
		#只获取了html，未完成解析
		case 1:
		{
			//解析html
			$args = www_youm7_com_ResolveHtml($args);

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

function www_youm7_com_ResolveHtml($data)
{
	//解析页面：使用phpQuery[默认]、simple_html_dom、正则表达式处理

    //处理内容页面
	phpQuery::newDocumentHTML($data['html']);

	//提取选定内容
	$article = pq('.news-content');

	//清理页面内容
	$article = removeHtml($article);

	//title
    $title = $article->find('h1')->text();

    //time
    $time = $article->find('.newsStoryDate')->text();
    $time = strReplaceNT($time);

    //content
    // $content = $article->find('.articleCont')->html();
    $content = $article->find('.articleCont p')->htmlOuter();

    //特殊处理
    // $content = preg_replace('/<figure([^>]*?)>/i', '<p>', $content);//处理头图部分
    // $content = preg_replace('/<\/figure>/i', '</p>', $content);

    //content正则过滤[函数内有重用过滤正则]
    $patten = array(
    		'/<ul class="share_icons".*<\/ul>/ism',
    		'/<span class="writeBy".<\/span>*/i',
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
$operatorID['www.youm7.com'] = 0;

// urlList配置文件【网站有防采集！】
$urlInfo['www.youm7.com'] = [
	#0
	[
		'url' => 'http://www.youm7.com/Section/%D8%A3%D8%AE%D8%A8%D8%A7%D8%B1-%D8%B9%D8%A7%D8%AC%D9%84%D8%A9/65/1',
		'tag' => 5,
		'type' => 1,
		'diff' => 1,
	],
	#1
	[
		'url' => 'http://www.youm7.com/Section/%D8%B3%D9%8A%D8%A7%D8%B3%D8%A9/319/1',
		'tag' => 5,
		'type' => 1,
		'diff' => 1,
	],
	#2
	[
		'url' => 'http://www.youm7.com/Section/%D8%AA%D9%82%D8%A7%D8%B1%D9%8A%D8%B1-%D9%85%D8%B5%D8%B1%D9%8A%D8%A9/97/1',
		'tag' => 5,
		'type' => 1,
		'diff' => 1,
	],
	#3
	[
		'url' => 'http://www.youm7.com/Section/%D8%AD%D9%88%D8%A7%D8%AF%D8%AB/203/1',
		'tag' => 5,
		'type' => 1,
		'diff' => 1,
	],
	#4
	[
		'url' => 'http://www.youm7.com/Section/%D8%A3%D8%AE%D8%A8%D8%A7%D8%B1-%D8%A7%D9%84%D9%85%D8%AD%D8%A7%D9%81%D8%B8%D8%A7%D8%AA/296/1',
		'tag' => 5,
		'type' => 1,
		'diff' => 1,
	],
	#5
	[
		'url' => 'http://www.youm7.com/Section/%D8%AA%D8%AD%D9%82%D9%8A%D9%82%D8%A7%D8%AA-%D9%88%D9%85%D9%84%D9%81%D8%A7%D8%AA/12/1',
		'tag' => 5,
		'type' => 1,
		'diff' => 1,
	],
	#6
	[
		'url' => 'http://www.youm7.com/Section/%D8%A3%D8%AE%D8%A8%D8%A7%D8%B1-%D8%A7%D9%84%D8%B1%D9%8A%D8%A7%D8%B6%D8%A9/298/1',
		'tag' => 5,
		'type' => 1,
		'diff' => 1,
	],
];






