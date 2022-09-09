<?php
namespace infrastructure\core;

use Dotenv\Dotenv;
use infrastructure\core\exception\InvalidRequestException;
use infrastructure\core\general\MapRequest;
use infrastructure\core\interfaces\iRunner;
use infrastructure\core\traits\Singleton;
use infrastructure\core\attributes\Cache;
use infrastructure\core\interfaces\iAttribute;
use infrastructure\core\attributes\Transactional;
use infrastructure\core\enums\ResponseType;
use infrastructure\core\http\ResponseReturn;

class PotatoCore {

    use Singleton;

    /**
     * PotatoCore constructor.
     * @throws \Exception
     */
    public function __construct(){
        self::$instance = $this;

        $dotenv = Dotenv::createImmutable(INFRA_PATCH );
        $dotenv->load();

        $this->setLocale();

        if ($_ENV['FORCE_SSL']){
            $this->sslRedirect();
        }

        $this->initDatabase();

        $GetRunner = new $_ENV['RUNNER_CLASS']();
        if (!$GetRunner instanceof iRunner){
            throw new InvalidRequestException();
        }

        $GetRunner->main();

        if (request()->activeRoute == null) {
            throw new InvalidRequestException();
        }

        $this->execute();

        http_response_code(request()->activeRoute->statusCode->value);
    }

    private function execute(){
        $class = request()->activeRoute->getClass();
        $method = request()->activeRoute->getMethod();
        $attrs = request()->activeRoute->getParams();

        if (class_exists($class)) {
            $initClass = new $class();
            $verifyClass = new \ReflectionClass($initClass);


            $properties = $verifyClass->getProperties();
            reflection_properties($initClass, $properties);

            $MethodRef = $verifyClass->getMethod($method);
            $totalParams = $MethodRef->getParameters();
            $Attributes = $MethodRef->getAttributes();

            $HasCache = null;
            $HasTransactional = null;
            foreach($Attributes as $attribute) {
                $Build = $attribute->newInstance();
                if ($Build instanceof Cache) $HasCache = $Build;
                if ($Build instanceof Transactional) $HasTransactional = $Build;
                if ($Build instanceof iAttribute) $Build->execute();
            }

            $finalAttrs = [];
            foreach ($totalParams as $parameter) {
                $nameVar = $parameter->getName();

                $hasValueByMap = "";
                if ($parameter->getType()){
                    $tryClass = $parameter->getType()->getName();
                    if (class_exists($tryClass)){
                        $hasValueByMap = new $tryClass();
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

            try{
                $HasTransactional?->begins();

                if ($HasCache){
                    $HasCache->execute();
                    if ($HasCache->isInvalid){
                        outputBuffer()->start();
                        $execResource = call_user_func_array([$initClass, $method], $finalAttrs);
                        $this->renderView($execResource);
                        $HasCache->saveCache(outputBuffer()->returnAndClear());
                    }
                }else{
                    $execResource = call_user_func_array([$initClass, $method], $finalAttrs);
                    $this->renderView($execResource);
                }

                $HasTransactional?->commit();
            }catch (\Exception $exception){
                $HasTransactional?->rollback();
                throw $exception;
            }
        }
    }

    private function renderView(ResponseReturn|null $view, $return = false): mixed {
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

    public function loadHelper($file){
        $isFind = false;
        if (file_exists(INFRA_PATCH."Helpers/".$file.".php")) {
            require(INFRA_PATCH . "Helpers/" . $file . ".php");
            $isFind = true;
        }
        if (!$isFind){
            throw new \Exception("File Helper {$file} not found");
        }
    }

    private function initDatabase(){
        /**
         * @var $Driver \System\Database\DriverImplements
         */
        $Driver = new $_ENV["DATABASE_DRIVE"]();
        $Driver->createConnection();
    }

    private function sslRedirect(){
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
            $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $location);
            exit;
        }
    }

    private function setLocale(){
        putenv("LC_ALL=".$_ENV['LANG']);
        setlocale(LC_ALL, $_ENV['LANG']);
        bindtextdomain($_ENV['LANG_DOMAIN'], INFRA_PATCH . '/i18n');
        bind_textdomain_codeset($_ENV['LANG_DOMAIN'], $_ENV['LANG_ENCODING']);
        textdomain($_ENV['LANG_DOMAIN']);
    }

    public function systemLangCreator(){
        moGenerator(INFRA_PATCH . '/i18n/'.$_ENV['LANG']."/LC_MESSAGES/".$_ENV['LANG_DOMAIN']);
    }
}
