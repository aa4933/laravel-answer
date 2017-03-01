<?php
function s($keyword,$url,$page = 1){
    static $px = 0;
    $rsState = false;

    $enKeyword = urlencode($keyword);
    $firstRow = ($page - 1) * 10;

    if($page > 2){
        die('10页之内没有该网站排名..end');
    }
    $contents = file_get_contents("http://www.baidu.com/s?wd=$enKeyword&&pn=$firstRow");
    preg_match_all('/<div[^>]*?id="content_left"[^>]*>[\s\S]*?<\/div>/i',$contents,$rs);


    foreach($rs[0] as $k=>$v){
        $px++;

        if(strstr($v,$url)){
            $rsState = true;

            preg_match_all('/<h3[\s\S]*?(<a[\s\S]*?<\/a>)/',$v,$rs_t);


            echo '当前 "' . $url . '" 在百度关键字 "' . $keyword . '" 中的排名为：' . $px;
            echo '<br>';
            echo '第' . $page . '页;第' . ++$k . "个<a target='_blank' href='http://www.baidu.com/s?wd=$enKeyword&&pn=$firstRow'>进入百度</a>";
            echo '<br>';
            //PRINT_r($rs_t);die;
            echo $rs_t[1][0];
            //echo iconv('GBK','UTF-8//IGNORE',$rs_t[1][0]);
            break;
        }
    }
    unset($contents);
    if($rsState === false){
        s($keyword, $url,++$page);
    }
}
if(isset($_POST['submit'])){

    $time = explode(' ',microtime());
    $start = $time[0] + $time[1];

    $url = $_POST['url'];
    if( count(explode('.',$url)) <= 2){

        $url = ltrim($url,'http://');
        $url = 'www.' . $url;
    }


    s($_POST['keyword'],$url);

    $endtime = explode(' ',microtime());

    $end = $endtime[0] + $endtime[1];

    echo '<hr>';
    echo '程序运行时间: ';
    echo $end - $start;
    die();
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>抓取排名</title>

</head>

<body>
<form action="" method="post">
    <ul>
        <li>
            <span>关键字：</span><input type="text" name="keyword">
        </li>
        <li>
            <span>url地址：</span><input type="text" name="url">
        </li>
        <li>
            <input type="submit" name="submit" value="搜索">
        </li>
    </ul>

</form>
</body>
</html>
/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/

require __DIR__.'/../bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
