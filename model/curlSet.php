<?php 
defined('HOST_PATH') or exit("contorl path error");

function setCurlOPT($control, $curl)
{
    $cpCurl = $curl;
    global $setCookie;//放到db_temp.php里面了

    if (strlen($control) < 1) {
        echo "#ERROR : domainOPT.setCurlOPT  \$control is null .....";
    }
    switch ($control) {
        case 'www.alarabiya.net':
            #服务器用curl https://www.alarabiya.net/arab-and-world/egypt.html
            #返回内容里面有cookie的信息，更换后面ip地址52.53.165.224为最新信息，示例如下：
            #setCookie('YPF8827340282Jdskjhfiw_928937459182JAX666', '52.53.165.224', 10);
            $cpCurl->opt [CURLOPT_COOKIE] = $setCookie[$control];
            break;
        case 'www.gheir.com':
            $cpCurl->opt [CURLOPT_COOKIE] = $setCookie[$control];
            $cpCurl->opt [CURLOPT_ENCODING] = 'gzip';
            break;
        case 'www.al-jazirah.com':
            $cpCurl->opt [CURLOPT_POST] = 1;
            $cpCurl->opt [CURLOPT_POSTFIELDS] = '';
            break;
        default:
            $cpCurl->opt [CURLOPT_COOKIE] = '';
            $cpCurl->opt [CURLOPT_ENCODING] = '';
            $cpCurl->opt [CURLOPT_POSTFIELDS] = '';
            $cpCurl->opt [CURLOPT_POST] = '';
            break;
    }
    return $cpCurl;
}
