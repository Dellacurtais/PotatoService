<?php

use Gettext\Generator\MoGenerator;
use Gettext\Loader\MoLoader;
use Gettext\Loader\PoLoader;
use infrastructure\core\general\OutputBuffer;
use infrastructure\core\general\Session;
use infrastructure\core\general\Smarty;
use infrastructure\core\http\Request;
use infrastructure\core\http\Response;
use infrastructure\core\http\Routes;
use infrastructure\core\interfaces\iCache;
use infrastructure\core\PotatoCore;

function core(): PotatoCore {
    return PotatoCore::getInstance();
}

function response(): Response {
    return Response::getInstance();
}

function request(): Request {
    return Request::getInstance();
}

function routes(): Routes {
    return Routes::getInstance();
}

function smarty(): Smarty {
    return Smarty::getInstance();
}

function session(): Session {
    return Session::getInstance();
}

function cacheService(): iCache {
    return new $_ENV['CACHE_SERVICE']();
}

function outputBuffer(): OutputBuffer {
    return OutputBuffer::getInstance();
}

function getUriPatch(): string{
    $str = str_replace_first($_ENV['BASE_DIR'], "", $_SERVER['REQUEST_URI']);
    return str_replace([$_SERVER['QUERY_STRING'], "?"], "", $str);
}

function str_replace_first($from, $to, $content): array|string|null{
    $from = '/' . preg_quote($from, '/') . '/';
    return preg_replace($from, $to, $content, 1);
}

function jsonBody(): array|null {
    return json_decode(file_get_contents('php://input'), 1);
}

function getRequestHeaders(): array|null{
    $headers = array();
    $copy_server = array(
        'CONTENT_TYPE'   => 'Content-Type',
        'CONTENT_LENGTH' => 'Content-Length',
        'CONTENT_MD5'    => 'Content-Md5',
    );
    foreach ($_SERVER as $key => $value) {
        if (str_starts_with($key, 'HTTP_')) {
            $key = substr($key, 5);
            if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                $headers[$key] = $value;
            }
        } elseif (isset($copy_server[$key])) {
            $headers[$copy_server[$key]] = $value;
        }
    }
    if (!isset($headers['Authorization'])) {
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
            $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
            $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
        } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
            $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
        }
    }
    return $headers;
}

function reflection_properties(&$object, $properties): void {
    foreach ($properties as $property){
        $propertyName = $property->getName();
        $Attributes = $property->getAttributes();
        foreach($Attributes as $attribute) {
            $Build = $attribute->newInstance();
            if ($Build instanceof \infrastructure\core\attributes\Autowired){
                $object->$propertyName = $Build->getClass();
            }
        }
    }
}

function getRequestIp(): string|null {
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        return $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        return $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        return $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        return $_SERVER['REMOTE_ADDR'];
    else
        return 'UNKNOWN';
}

function getRequestInfo(string|null $u_agent = null, string|null $ip = null){
    $ip = $ip ?? getClientIpServer();
    $u_agent = $u_agent ?? $_SERVER['HTTP_USER_AGENT'];

    $bname = 'Unknown';
    $platform = 'Unknown';
    $version = "";

    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'Linux';
    }elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'Mac';
    }elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'Windows';
    }

    if(preg_match('/Edge/i',$u_agent)) {
        $bname = 'Edge';
        $ub = "Edge";
    }elseif(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }elseif(preg_match('/Trident/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "Trident";
    }elseif(preg_match('/Firefox/i',$u_agent)) {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }elseif(preg_match('/Chrome/i',$u_agent)) {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }elseif(preg_match('/AppleWebKit/i',$u_agent)) {
        $bname = 'AppleWebKit';
        $ub = "Opera";
    }elseif(preg_match('/Safari/i',$u_agent)) {
        $bname = 'Apple Safari';
        $ub = "Safari";
    }elseif(preg_match('/Netscape/i',$u_agent)) {
        $bname = 'Netscape';
        $ub = "Netscape";
    }

    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

    preg_match_all($pattern, $u_agent, $matches);
    if (isset($matches['browser'])){
        $i = count($matches['browser']);
        if ($i != 1) {
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            }else{
                $version= $matches['version'][1];
            }
        }else{
            $version= $matches['version'][0];
        }
        if ($ub == "Trident"){
            preg_match('#rv:([0-9.|a-zA-Z.]*)#',$u_agent, $versions);
            $version = $versions[1];
        }
    }

    if ($version==null || $version=="")
        $version="?";

    return array(
        'ip' => $ip,
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}

function moGenerator($langFile){
    if (file_exists($langFile.".po")){
        $loader = new PoLoader();
        $translations = $loader->loadFile($langFile.".po");

        $generator = new MoGenerator();
        $generator->generateFile($translations, $langFile.".mo");
    }
}