<?php 

//浏览器信息
function getUserAgentInfo()
{
	$useragentInfo = array(
		// 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)',
		// 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2)',
		// 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)',
		// 'Mozilla/5.0 (Windows; U; Windows NT 5.2) Gecko/2008070208 Firefox/3.0.1',
		// 'Opera/9.27 (Windows NT 5.2; U; zh-cn)',
		// 'Opera/8.0 (Macintosh; PPC Mac OS X; U; en)',
		'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.2.149.27 Safari/525.13 ',
		'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Version/3.1 Safari/525.13',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36'
	);
	return array_rand($useragentInfo);
}

function getUserIP()
{
	//IP信息
	$r = rand(1,230);
	$userIP = array('X-FORWARDED-FOR:172.96.113.'.$r, 'CLIENT-IP:172.96.113.'.$r);
	return $userIP;
}

function getUserReferer()
{
	//来路信息
	$userRefererInfo = array(
		'http://www.baidu.com',
		'http://www.google.com'
	);
	return array_rand($userRefererInfo);
}



