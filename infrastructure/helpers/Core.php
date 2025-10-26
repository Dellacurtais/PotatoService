<?php

use Gettext\Generator\MoGenerator;
use Gettext\Loader\PoLoader;
use infrastructure\core\attributes\Transactional;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\general\OutputBuffer;
use infrastructure\core\general\Session;
use infrastructure\core\general\Smarty;
use infrastructure\core\http\Request;
use infrastructure\core\http\Response;
use infrastructure\core\http\ResponseHtml;
use infrastructure\core\http\ResponseJson;
use infrastructure\core\http\Routes;
use infrastructure\core\interfaces\iAttribute;
use infrastructure\core\interfaces\iCache;
use infrastructure\core\PotatoCore;

function core(): PotatoCore {
    return PotatoCore::getInstance();
}

function response(): Response {
    return Response::getInstance();
}

function responseJson(): ResponseJson {
    return Response::getInstance()->json();
}

function responseCsv(): \infrastructure\core\http\ResponseCsv {
    return Response::getInstance()->csv();
}

function responseHtml(): ResponseHtml {
    return Response::getInstance()->html();
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
    if (!is_array($properties)) return;

    // Detect if we're dealing with the new cached metadata format or old ReflectionProperty[]
    $first = reset($properties);

    // New format: [ 'propName' => [ ['class' => FQCN, 'args' => [...]], ... ] ]
    if ($first === false || is_array($first)) {
        foreach ($properties as $propertyName => $attrs) {
            if (!is_array($attrs)) continue;
            foreach ($attrs as $attr) {
                $attrClass = $attr['class'] ?? null;
                $args = $attr['args'] ?? [];
                if ($attrClass === \infrastructure\core\attributes\Autowired::class) {
                    // Ensure args is a list (positional) as expected by Autowired::__construct($class, $args = [])
                    if (!is_array($args)) {
                        $args = [$args];
                    } else {
                        // keep only values to drop any named keys
                        $args = array_values($args);
                    }
                    $build = new \infrastructure\core\attributes\Autowired(...$args);
                    $object->$propertyName = $build->getClass();
                }
            }
        }
        return;
    }

    // Old format: array of ReflectionProperty
    foreach ($properties as $property){
        if (!$property instanceof \ReflectionProperty) continue;
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

/**
 * Executes a method of an object, processing its attributes before the actual method execution.
 * If the Transactional attribute is present, the method execution is wrapped in a transaction.
 *
 * @param object $object The object containing the method to be executed.
 * @param string $methodName The name of the method to be executed.
 * @param array $params The parameters to be passed to the method.
 * @return mixed The result of the method execution.
 * @throws \Exception Throws any exceptions that might occur during method or transaction execution.
 */
function doFilter($object, string $methodName, array $params = []): mixed {
    $method = new \ReflectionMethod(get_class($object), $methodName);
    $attributes = $method->getAttributes();

    $transactionalInstance = null;
    foreach ($attributes as $attribute) {
        $attributeInstance = $attribute->newInstance();
        if ($attributeInstance instanceof Transactional) {
            $transactionalInstance = $attributeInstance;
        } else if ($attributeInstance instanceof iAttribute){
            $attributeInstance->execute();
        }else {
            throw new BusinessException('The provided attribute instance does not implement the iAttribute interface.');
        }
    }

    if ($transactionalInstance) {
        return handleTransactional($transactionalInstance, $method, $object, $params);
    }

    return $method->invokeArgs($object, $params);
}

/**
 * Handles the execution of a method within a transaction.
 *
 * @param object $transactionalInstance The Transactional attribute instance.
 * @param \ReflectionMethod $method The method to be executed.
 * @param object $object The object containing the method.
 * @param array $params The parameters to be passed to the method.
 * @return mixed The result of the method execution.
 * @throws \Exception Throws any exceptions that might occur during method or transaction execution.
 */
function handleTransactional($transactionalInstance, $method, $object, array $params = []) {
    try {
        $transactionalInstance->begins();
        $result = $method->invokeArgs($object, $params);
        $transactionalInstance->commit();
        return $result;
    } catch (\Exception $e) {
        $transactionalInstance->rollback();
        throw $e;
    }
}

function moGenerator($langFile){
    if (file_exists($langFile.".po")){
        $loader = new PoLoader();
        $translations = $loader->loadFile($langFile.".po");

        $generator = new MoGenerator();
        $generator->generateFile($translations, $langFile.".mo");
    }
}

function removeFiles($dir, $removeDir = false): void {
    if (is_dir($dir)) {
        $itens = scandir($dir);
        foreach ($itens as $item) {
            if ($item != "." && $item != "..") {
                removeFiles($dir . DIRECTORY_SEPARATOR . $item);
            }
        }
        if ($removeDir)
            rmdir($dir);
    } else if($removeDir) {
        unlink($dir);
    }
}

/**
 * @throws Exception
 */
function n_date(string $format): string {
    $microtime = microtime(true);
    $milli = sprintf("%03d",($microtime - floor($microtime)) * 1000);
    $date = new DateTime(date('Y-m-d H:i:s') .'.'.$milli);
    return $date->format($format);
}

function logError($message): void {
    if (PHP_SAPI !== 'cli') return;

    coloredEcho(n_date('Y-m-d H:i:s.v')."[".getmytid() ."]".": ", 'yellow');
    coloredEcho(_($message), 'red');
    echo "\n";
}

function logWarning($message): void {
    if (PHP_SAPI !== 'cli') return;

    coloredEcho(n_date('Y-m-d H:i:s.v')."[".getmytid() ."]".": ", 'yellow');
    coloredEcho(_($message), 'yellow');
    echo "\n";
}

function logSuccess($message): void {
    if (PHP_SAPI !== 'cli') return;

    coloredEcho(n_date('Y-m-d H:i:s.v')."[".getmytid() ."]".": ", 'yellow');
    coloredEcho(_($message), 'green');
    echo "\n";
}

function logInfo($message): void {
    if (PHP_SAPI !== 'cli') return;

    coloredEcho(n_date('Y-m-d H:i:s.v')."[".getmytid() ."]".": ", 'yellow');
    coloredEcho(_($message));
    echo "\n";
}

function getmytid(): string {
    if (defined('TID')){
        return getmypid() . '-' . TID;
    }
    return getmypid() . '-MAIN';
}

function coloredEcho($text, $color = "default"): void {
    $colors = [
        'default' => "\033[0m",
        'black' => "\033[0;30m",
        'red' => "\033[0;31m",
        'green' => "\033[0;32m",
        'yellow' => "\033[0;33m",
        'blue' => "\033[0;34m",
        'purple' => "\033[0;35m",
        'cyan' => "\033[0;36m",
        'white' => "\033[0;37m"
    ];

    echo $colors[$color] . $text . $colors['default'];
}

function jsonError($data) : string{
    return json_encode(['error' => $data]);
}

function jsonSuccess($data): string{
    return json_encode(['success' => $data]);
}

function trace(): void {
    $e = new Exception();
    throw new Exception($e->getTraceAsString());
}

function clearUri($uri): string {
    $uri = str_replace('//', '/', $uri);
    return rtrim($uri, '/');
}

function getTimeSinceInit(): float {
    return (microtime(true) - INIT_REQUEST) * 1000;
}

function profiler_enabled(): bool {
    // Default disabled if env not loaded yet
    $flag = $_ENV['DEBUG_PROFILER'] ?? $_SERVER['DEBUG_PROFILER'] ?? null;
    if ($flag === null) return false;
    if (is_bool($flag)) return $flag;
    $flag = strtolower((string)$flag);
    return in_array($flag, ['1','true','yes','on'], true);
}

function profiler_mark(string $label): void {
    if (!profiler_enabled()) return;
    static $marks = null;
    if ($marks === null) {
        $marks = [];
    }
    $marks[] = [
        't' => getTimeSinceInit(),
        'mem' => memory_get_usage(true),
        'label' => $label,
    ];
    $GLOBALS['__POTATO_PROFILER_MARKS'] = $marks;
}

function profiler_report(): array {
    if (!profiler_enabled()) return [];
    $marks = $GLOBALS['__POTATO_PROFILER_MARKS'] ?? [];
    $out = [];
    $prevT = null;
    foreach ($marks as $m) {
        $delta = $prevT === null ? 0.0 : ($m['t'] - $prevT);
        $out[] = [
            'label' => $m['label'],
            't_ms' => round($m['t'], 3),
            'delta_ms' => round($delta, 3),
            'mem_bytes' => $m['mem'],
        ];
        $prevT = $m['t'];
    }
    return $out;
}

function dd(...$var){
    var_dump($var);
    die();
}

if (!function_exists('now')){
    function now(): \Carbon\Carbon
    {
        return \Carbon\Carbon::now();
    }
}
