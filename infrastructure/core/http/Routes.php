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

    public static function registerResources(array $controllers){
        $selfInstance = self::getInstance();
        foreach($controllers as $controller) {
            $reflectionController = new \ReflectionClass($controller);

            foreach($reflectionController->getMethods() as $method) {
                $attribute = current($method->getAttributes(Route::class, \ReflectionAttribute::IS_INSTANCEOF));
                $route = $attribute->newInstance();
                $routeMap = new RouteMap($route->type, $route->route, [$controller, $method->getName()], $route->alias, $route->headers, $route->requireHeader);
                $routeMap->setStatusCode($route->code);
                if ($routeMap->validate(request()->requestUri)){
                    request()->activeRoute = $routeMap;
                }

                $selfInstance->routes[] = $routeMap;
            }
        }
    }

    public function alias(string $name, array $args = []): RouteMap|null {
        $hasAlias = array_filter($this->routes, fn($route) => $route->alias == $name );
        return $hasAlias[0]->toUri($args) ?? null;
    }

}