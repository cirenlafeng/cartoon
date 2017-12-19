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

		//获取所有图片
		$dir = "C:\wamp64\www\cartoon\control\sup78";  //要获取的目录
		$flag = getPregData('/control(.*?)$/ism',$dir).'/';
		$flag = str_replace('\\','',$flag);
		$sqlData = array();
		//先判断指定的路径是不是一个文件夹
		if (is_dir($dir)){
			$temp = [];
			$temp['list_id'] = 638;
			$temp['domain'] = 'www.manga.ae';
			$temp['chapter_name'] = '-';
			$temp['chapter'] = 78;

			if ($dh = opendir($dir)){
				$a=0;
				while (($file = readdir($dh))!= false){
					
					//文件名的全路径 包含文件名
					if($file == '.' || $file == '..') continue;
					if(!preg_match('/\.jpg/',$file)) continue;
					$a++;
					$filePath = $dir.$file;
					$temp['page'] = $a;
					$temp['thumbnail'] = 'http://localhost/cartoon/control/'.$flag.$file;
					$temp['width'] = 610;
					$temp['height'] = 1000;
					$temp['status'] = 0;
					$temp['pagecount'] = 24;
					//echo $filePath.PHP_EOL;
					$sqlData[] = $temp;
				}
				
				
				closedir($dh);
			}
		}
		saveUrlList($sqlData, $args);
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
		$postData = [
    'appName'=>'sada',
    'type'=>'file',
    'w'=>610,
    'h'=>1000,
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



