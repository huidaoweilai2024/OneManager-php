<?php
//error_reporting(E_ALL & ~E_NOTICE);
error_reporting(0);

include 'vendor/autoload.php';
include 'conststr.php';
include 'common.php';

date_default_timezone_set('UTC');
//echo '<pre>'. json_encode($_SERVER, JSON_PRETTY_PRINT).'</pre>';
//echo '<pre>'. json_encode($_ENV, JSON_PRETTY_PRINT).'</pre>';
if (!function_exists('curl_init')) {
    http_response_code(500);
    echo '<font color="red">Need curl</font>, please install php-curl.';
    exit(1);
}
global $platform;
$platform = checkPlatform();

function checkPlatform() {
    if (isset($_SERVER['USER']) && $_SERVER['USER'] === 'qcloud')
        return 'SCF';
    if (isset($_SERVER['FC_FUNC_CODE_PATH']))
        return 'FC';
    if (isset($_SERVER['RUNTIME_LOG_PATH']) && $_SERVER['RUNTIME_LOG_PATH'] == '/home/snuser/log')
        return 'FG';
    if (isset($_SERVER['BCE_CFC_RUNTIME_NAME']) && $_SERVER['BCE_CFC_RUNTIME_NAME'] == 'php7')
        return 'CFC';
    if (isset($_SERVER['HEROKU_APP_DIR']) && $_SERVER['HEROKU_APP_DIR'] === '/app')
        return 'Heroku';
    if (isset($_ENV["VERCEL_ENV"]))
        return 'Vercel';
    if (isset($_SERVER['DOCUMENT_ROOT']) && substr($_SERVER['DOCUMENT_ROOT'], 0, 13) === '/home/runner/')
        return 'Replit';
    if (isset($_ENV["PANTHEON_ENVIRONMENT"]))
        return 'Pantheon';
    return 'Normal';
}

function writebackPlatform($p) {
    if ('SCF' == $p) $_SERVER['USER'] = 'qcloud';
    if ('FC' == $p) $_SERVER['FC_FUNC_CODE_PATH'] = getenv('FC_FUNC_CODE_PATH');
    if ('FG' == $p) $_SERVER['RUNTIME_LOG_PATH'] = '/home/snuser/log';
    if ('CFC' == $p) $_SERVER['BCE_CFC_RUNTIME_NAME'] = 'php7';
}

if ('SCF' == $platform) {
    if (getenv('ONEMANAGER_CONFIG_SAVE') == 'file') include 'platform/TencentSCF_file.php';
    else include 'platform/TencentSCF_env.php';
} elseif ('FC' == $platform) {
    include 'platform/AliyunFC.php';
} elseif ('FG' == $platform) {
    echo 'FG' . PHP_EOL;
} elseif ('CFC' == $platform) {
    include 'platform/BaiduCFC.php';
} elseif ('Heroku' == $platform) {
    include 'platform/Heroku.php';
    $path = getpath();
    $_GET = getGET();
    $re = main($path);
    foreach ($re['headers'] as $headerName => $headerVal) {
        header($headerName . ': ' . $headerVal, true);
    }
    http_response_code($re['statusCode']);
    if ($re['isBase64Encoded']) echo base64_decode($re['body']);
    else echo $re['body'];
} elseif ('Vercel' == $platform) {
    if (getenv('ONEMANAGER_CONFIG_SAVE') == 'env') include 'platform/Vercel_env.php';
    else include 'platform/Vercel.php';

    $path = getpath();
    $_GET = getGET();
    $re = main($path);
    foreach ($re['headers'] as $headerName => $headerVal) {
        header($headerName . ': ' . $headerVal, true);
    }
    http_response_code($re['statusCode']);
    if ($re['isBase64Encoded']) echo base64_decode($re['body']);
    else echo $re['body'];
} elseif ('Replit' == $platform) {
    include 'platform/Replit.php';
    $path = getpath();
    $_GET = getGET();
    $re = main($path);
    foreach ($re['headers'] as $headerName => $headerVal) {
        header($headerName . ': ' . $headerVal, true);
    }
    http_response_code($re['statusCode']);
    if ($re['isBase64Encoded']) echo base64_decode($re['body']);
    else echo $re['body'];
} elseif ('Pantheon' == $platform) {
    include 'platform/Pantheon.php';
    $path = getpath();
    $_GET = getGET();
    $re = main($path);
    foreach ($re['headers'] as $headerName => $headerVal) {
        header($headerName . ': ' . $headerVal, true);
    }
    http_response_code($re['statusCode']);
    if ($re['isBase64Encoded']) echo base64_decode($re['body']);
    else echo $re['body'];
} else {
    include 'platform/Normal.php';
    $path = getpath();
    $_GET = getGET();
    $re = main($path);
    foreach ($re['headers'] as $headerName => $headerVal) {
        header($headerName . ': ' . $headerVal, true);
    }
    http_response_code($re['statusCode']);
    if ($re['isBase64Encoded']) echo base64_decode($re['body']);
    else echo $re['body'];
}

// Tencent SCF
function main_handler($event, $context) {
    $event = json_decode(json_encode($event), true);
    $context = json_decode(json_encode($context), true);
    printInput($event, $context);
    if ($event['requestContext']['serviceId'] === substr($event['headers']['host'], 0, strlen($event['requestContext']['serviceId']))) {
        if ($event['path'] === '/' . $context['function_name']) return output('add / at last.', 308, ['Location' => '/' . $event['requestContext']['stage'] . '/' . $context['function_name'] . '/']);
    }
    unset($_POST);
    unset($_GET);
}
