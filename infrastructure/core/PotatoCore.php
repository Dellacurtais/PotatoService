<?php
namespace infrastructure\core;

use Dotenv\Dotenv;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\exception\InvalidRequestException;
use infrastructure\core\interfaces\iRunner;
use infrastructure\core\traits\Singleton;
use infrastructure\core\attributes\Cache;
use infrastructure\core\interfaces\iAttribute;
use infrastructure\core\attributes\Transactional;
use infrastructure\core\enums\ResponseType;
use infrastructure\core\http\ResponseReturn;
use infrastructure\core\general\ReflectionCache;

class PotatoCore {

    use Singleton;

    public $version;

    /**
     * PotatoCore constructor.
     * @throws \Exception
     */
    public function __construct(){
        profiler_mark("InitCore");
        self::$instance = $this;
        profiler_mark("InitCore");
        $dotenv = Dotenv::createImmutable(INFRA_PATCH );
        $dotenv->load();

        if (PHP_SAPI === 'cli'){
            if (count($_ENV) === 0){
                $_ENV = $_SERVER;
            }
            $this->executeCli();
            exit();
        }

        $GetRunner = new $_ENV['RUNNER_CLASS']();
        if (!$GetRunner instanceof iRunner){
            throw new InvalidRequestException();
        }

        $GetRunner->onStart();

        $this->version = file_get_contents(INFRA_PATCH . '/cache/.currentVersion');
        if ($this->version != $_ENV['VERSION']){
            core()->systemLangCreator();
            file_put_contents(INFRA_PATCH . '/cache/.currentVersion', $_ENV['VERSION']);

            $GetRunner->onDetectNewVersion();
        }

        $this->setLocale();

        if ($_ENV['FORCE_SSL']){
            $this->sslRedirect();
        }

        $this->initDatabase();

        $GetRunner->afterDatabaseConnection();

        if (request()->activeRoute == null)
            $GetRunner->main();

        if (request()->activeRoute == null)
            throw new InvalidRequestException();

        http_response_code(request()->activeRoute->statusCode->value);

        $this->execute();

        $GetRunner->onFinish();
    }

    private function executeCli(): void {
        $GetRunner = new $_ENV['RUNNER_CLASS_CONSOLE']();
        if (!$GetRunner instanceof iRunner){
            logError("Error starting console. Check the RUNNER_CLASS_CONSOLE in the .env");
            exit();
        }
        $GetRunner->onStart();

        $this->setLocale();
        //$this->initDatabase();
        //$GetRunner->afterDatabaseConnection();

        $GetRunner->main();

        $GetRunner->onFinish();
    }

    private function execute(): void {
        $class = request()->activeRoute->getClass();
        $method = request()->activeRoute->getMethod();
        $attrs = request()->activeRoute->getParams();

        if (class_exists($class)) {
            $meta = ReflectionCache::get($class);

            $ctorArgs = [];
            $ctorParams = $meta['constructor']['parameters'] ?? [];
            foreach ($ctorParams as $p) {
                $nameVar = $p['name'] ?? '';
                $hasValueByMap = "";
                $typeName = $p['type'] ?? null;
                $isBuiltin = $p['isBuiltin'] ?? true;
                if ($typeName && !$isBuiltin && class_exists($typeName, true)) {
                    $hasValueByMap = new $typeName();
                }
                if ($nameVar === 'request') {
                    $ctorArgs[] = request();
                } elseif ($nameVar === 'response') {
                    $ctorArgs[] = response();
                } else {
                    $ctorArgs[] = $hasValueByMap;
                }
            }
            $initClass = new $class(...$ctorArgs);
            reflection_properties($initClass, $meta['properties'] ?? []);

            $methodMeta = $meta['methods'][$method] ?? ['attributes' => [], 'parameters' => []];
            $Attributes = $methodMeta['attributes'] ?? [];

            $HasCache = null;
            $hasTransactional = null;
            foreach ($Attributes as $attribute) {
                $attrClass = $attribute['class'] ?? null;
                if (!$attrClass || !class_exists($attrClass)) continue;
                $args = $attribute['args'] ?? [];

                $ordered = [];
                $attrMeta = ReflectionCache::get($attrClass);
                $ctorParamsAttr = $attrMeta['constructor']['parameters'] ?? [];
                foreach ($ctorParamsAttr as $idx => $p) {
                    $pName = $p['name'] ?? '';
                    if (array_key_exists($pName, $args)) {
                        $ordered[] = $args[$pName];
                        continue;
                    }
                    if (array_key_exists($idx, $args)) {
                        $ordered[] = $args[$idx];
                        continue;
                    }
                    if (!empty($p['hasDefault']) && $p['hasDefault'] === true) {
                        $ordered[] = $p['default'] ?? null;
                        continue;
                    }
                    if (!empty($p['allowsNull'])) {
                        $ordered[] = null;
                        continue;
                    }

                    throw new BusinessException('Parâmetro obrigatório ausente ao instanciar atributo ' . $attrClass . '::$' . $pName);
                }

                $Build = new $attrClass(...$ordered);
                if ($Build instanceof Cache) $HasCache = $Build;
                if ($Build instanceof Transactional) $hasTransactional = $Build;
                if ($Build instanceof iAttribute) $Build->execute();
            }

            $finalAttrs = [];
            $totalParams = $methodMeta['parameters'] ?? [];
            foreach ($totalParams as $parameter) {
                $nameVar = $parameter['name'] ?? '';

                $hasValueByMap = "";
                $tryClass = $parameter['type'] ?? null;
                $isBuiltin = $parameter['isBuiltin'] ?? true;
                if ($tryClass && !$isBuiltin) {
                    if (class_exists($tryClass, true)){
                        $hasValueByMap = new $tryClass();
                    }else{
                        new BusinessException('Class '.$tryClass.' não existe');
                    }
                }

                if (isset($attrs[$nameVar])){
                    $finalAttrs[] = $attrs[$nameVar];
                }else{
                    $finalAttrs[] = match ($nameVar) {
                        'request' => request(),
                        'response' => response(),
                        default => $hasValueByMap
                    };
                }
            }

            $hasTransactional?->begins();
            try{
                if ($HasCache && !$HasCache->isInvalid){
                    $HasCache->execute();
                }else{
                    if ($HasCache)
                        outputBuffer()->start();

                    $execResource = call_user_func_array([$initClass, $method], $finalAttrs);
                    $this->renderView($execResource);

                    if ($HasCache){
                        $HasCache->saveCache(outputBuffer()->returnAndClear());
                    }
                }
                $hasTransactional?->commit();
            }catch (\Exception $exception){
                $hasTransactional?->rollback();
                throw $exception;
            }
        }
    }

    private function renderView(ResponseReturn|null $view, $return = false) {
        if ($view == null)
            return null;

        if ($view->getType() == ResponseType::HTML){
            $HasReturn = $this->setView($view->getView(), $view->getParams(), $return);
            if ($return)
                return $HasReturn;
        }else if ($view->getType() == ResponseType::JSON) {
            if ($return){
                return $view->toJson();
            }else{
                echo $view->toJson();
            }
        }
    }

    private function setView(string $file, array $data = [], bool $return = false): ?string {
        if ($_ENV['USE_SMARTY']) {
            return smarty()->view($file.".tpl", $data, $return);
        }else{
            extract($data);

            if ($return) outputBuffer()->start();

            include $_ENV['TEMPLATE_DIR'] . $file . ".php";

            if ($return)
                return outputBuffer()->returnAndClear();

            return null;
        }
    }

    public function loadHelper($file): void {
        $isFind = false;
        if (file_exists(INFRA_PATCH."Helpers/".$file.".php")) {
            require(INFRA_PATCH . "Helpers/" . $file . ".php");
            $isFind = true;
        }
        if (!$isFind){
            throw new \Exception("File Helper {$file} not found");
        }
    }

    private function initDatabase(): void {
        if ($_ENV["DISABLE_DATABASE"])
            return;

        /**
         * @var $Driver database\interfaces\iDriverImplements
         */
        $Driver = new $_ENV["DATABASE_DRIVE"]();

        $Driver->createConnection();
    }

    private function sslRedirect(): void {
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
            $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $location);
            exit;
        }
    }

    private function setLocale(): void {
        putenv("LC_ALL=".$_ENV['LANG']);
        setlocale(LC_ALL, $_ENV['LANG']);
        bindtextdomain($_ENV['LANG_DOMAIN'], INFRA_PATCH . '/i18n');
        bind_textdomain_codeset($_ENV['LANG_DOMAIN'], $_ENV['LANG_ENCODING']);
        textdomain($_ENV['LANG_DOMAIN']);
    }

    public function systemLangCreator(): void {
        moGenerator(INFRA_PATCH . '/i18n/'.$_ENV['LANG']."/LC_MESSAGES/".$_ENV['LANG_DOMAIN']);
    }
}
