<?php
namespace infrastructure\core\http;

use infrastructure\core\traits\Singleton;
use infrastructure\core\attributes\Route;

class Routes {

    use Singleton;

    /**
     * @var RouteMap[]
     */
    private array $routes = [];

    public static function registerResources(array $controllers): void {
        $selfInstance = self::getInstance();
        foreach($controllers as $controller) {
            $reflectionController = new \ReflectionClass($controller);

            self::verifyDir();

            foreach($reflectionController->getMethods() as $method) {
                $attribute = current($method->getAttributes(Route::class, \ReflectionAttribute::IS_INSTANCEOF));
                if ($attribute){
                    $route = $attribute->newInstance();
                    $routeMap = new RouteMap($route->type, $route->route, [$controller, $method->getName()], $route->alias, $route->headers, $route->requireHeader);
                    $routeMap->setStatusCode($route->code);
                    if ($routeMap->validate(request()->requestUri)){
                        request()->activeRoute = $routeMap;
                        self::createCache(request()->requestUri, $routeMap);
                    }
                    $selfInstance->routes[] = $routeMap;
                }
            }
        }

    }

    public function alias(string $name, array $args = []): RouteMap|null {
        $hasAlias = array_filter($this->routes, fn($route) => $route->alias == $name );
        return $hasAlias[0]->toUri($args) ?? null;
    }

    protected static function verifyDir(){
        if (!is_dir(INFRA_PATCH . '/cache/routes/')){
            mkdir(INFRA_PATCH . '/cache/routes', 0755);
        }
    }

    protected static function createCache(string $requestUri, RouteMap $routeMap){
        file_put_contents(INFRA_PATCH . '/cache/routes/'.$_SERVER['REQUEST_METHOD'].base64_encode($requestUri).'.cache', serialize($routeMap));
    }

    public static function verifyRouteCache(string $requestUri): RouteMap|null {
        if (file_exists(INFRA_PATCH . '/cache/routes/'.base64_encode($requestUri).'.cache')){
            return unserialize(file_get_contents(INFRA_PATCH . '/cache/routes/'.$_SERVER['REQUEST_METHOD'].base64_encode($requestUri).'.cache'));
        }
        return null;
    }

}