<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2017/4/7
 * Time: 下午3:34
 */

//禁止浏览器访问
if (PHP_SAPI != 'cli') {
    die('need cli');
}

if (empty($argv[1])){
    die('need json image url');
}

echo 'IMG URL === '.str_replace('\/','/',$argv[1]).PHP_EOL;