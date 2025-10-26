<?php
namespace infrastructure\core\http;

use infrastructure\core\attributes\BaseRoute;
use infrastructure\core\traits\Singleton;
use infrastructure\core\attributes\Route;
use infrastructure\core\general\ReflectionCache;

class Routes {

    use Singleton;

    /**
     * @var RouteMap[]
     */
    private array $routes = [];

    /**
     * @var array Mapa hash para lookup rápido de rotas estáticas
     */
    private array $staticRoutesMap = [];

    public static function registerResources(array $controllers, bool $onlyCache = false): void {
        $selfInstance = self::getInstance();
        self::verifyDir();

        // PRIORIDADE MÁXIMA: Verifica cache individual da rota primeiro
        if (!$onlyCache && !request()->activeRoute) {
            $cached = self::verifyRouteCache(request()->requestUri);
            if ($cached) {
                request()->activeRoute = $cached;
                return; // ⚡ Retorna imediatamente - cache hit!
            }
        }

        // Tenta carregar índice de rotas previamente compilado
        $cachedRoutes = self::loadRoutesIndex($controllers);
        if (is_array($cachedRoutes)) {
            $selfInstance->routes = $cachedRoutes;
            $selfInstance->buildStaticRoutesMap();

            if ($onlyCache) {
                // Em modo onlyCache, gerar cache apenas para rotas estáticas
                foreach ($selfInstance->routes as $routeMap) {
                    if (!$routeMap->isDinamic) {
                        self::createCache($routeMap->getRoute(), $routeMap);
                    }
                }
            } else {
                // Busca rota ativa usando lookup otimizado
                if (!request()->activeRoute) {
                    $routeMap = $selfInstance->findRoute(request()->requestUri);
                    if ($routeMap) {
                        request()->activeRoute = $routeMap;
                        self::createCache(request()->requestUri, $routeMap);
                    }
                }
            }
            return;
        }

        // Não há índice válido: constrói as rotas normalmente e persiste o índice
        $controllersMeta = [];
        foreach($controllers as $controller) {
            $meta = ReflectionCache::get($controller);
            if (!empty($meta['__file'])) {
                $controllersMeta[$controller] = ['file' => $meta['__file'], 'mtime' => $meta['__mtime'] ?? 0];
            }

            // BaseRoute da classe
            $baseRoute = "";
            foreach (($meta['class_attributes'] ?? []) as $attr) {
                if ($attr['class'] === BaseRoute::class) {
                    $baseRouteInstance = new BaseRoute(...$attr['args']);
                    $baseRoute = $baseRouteInstance->basePattern ?? '';
                    break;
                }
            }

            // Percorre os métodos e encontra os que possuem o atributo Route
            foreach (($meta['methods'] ?? []) as $methodName => $methodMeta) {
                $routeAttrArgs = null;
                foreach (($methodMeta['attributes'] ?? []) as $mAttr) {
                    if ($mAttr['class'] === Route::class) {
                        $routeAttrArgs = $mAttr['args'];
                        break;
                    }
                }

                if ($routeAttrArgs !== null) {
                    $route = new Route(...$routeAttrArgs);

                    $baseSeparator = $baseRoute != '' ? '/' : '';
                    $fullRoute = $baseRoute . $baseSeparator . $route->route;
                    $fullRoute = clearUri($fullRoute);

                    $routeMap = new RouteMap($route->type, $fullRoute, [$controller, $methodName], $route->alias, $route->headers, $route->requireHeader);
                    $routeMap->setStatusCode($route->code);

                    if ($onlyCache){
                        if (!$routeMap->isDinamic){
                            self::createCache($fullRoute, $routeMap);
                        }
                    } else {
                        if ($routeMap->validate(request()->requestUri)){
                            request()->activeRoute = $routeMap;
                            self::createCache(request()->requestUri, $routeMap);
                        }
                    }
                    $selfInstance->routes[] = $routeMap;
                }
            }
        }

        // Constrói mapa de rotas estáticas
        $selfInstance->buildStaticRoutesMap();

        // Persiste o índice de rotas já construído
        self::saveRoutesIndex($controllersMeta, $selfInstance->routes);
    }

    /**
     * Constrói mapa hash para lookup O(1) de rotas estáticas
     */
    private function buildStaticRoutesMap(): void {
        $this->staticRoutesMap = [];
        foreach ($this->routes as $routeMap) {
            if (!$routeMap->isDinamic) {
                $key = $this->getStaticRouteKey($routeMap->getType(), $routeMap->getRoute());
                $this->staticRoutesMap[$key] = $routeMap;
            }
        }
    }

    /**
     * Gera chave única para rota estática
     */
    private function getStaticRouteKey(string $method, string $route): string {
        return $method . '::' . $route;
    }

    /**
     * Busca rota usando estratégia otimizada:
     * 1. Tenta lookup direto no mapa de rotas estáticas (O(1))
     * 2. Se não encontrar, varre rotas dinâmicas (O(n))
     */
    private function findRoute(string $requestUri): ?RouteMap {
        $method = $_SERVER['REQUEST_METHOD'];

        // Tenta busca rápida em rotas estáticas
        $staticKey = $this->getStaticRouteKey($method, $requestUri);
        if (isset($this->staticRoutesMap[$staticKey])) {
            return $this->staticRoutesMap[$staticKey];
        }

        // Busca em rotas dinâmicas
        foreach ($this->routes as $routeMap) {
            if ($routeMap->isDinamic && $routeMap->validate($requestUri)) {
                return $routeMap;
            }
        }

        return null;
    }

    public function alias(string $name, array $args = []): RouteMap|null {
        $hasAlias = array_filter($this->routes, fn($route) => $route->alias == $name );
        return $hasAlias[0]->toUri($args) ?? null;
    }

    protected static function verifyDir(){
        if (!is_dir(INFRA_PATCH . '/cache/routes/')){
            mkdir(INFRA_PATCH . '/cache/routes', 0755, true);
        }
    }

    protected static function createCache(string $requestUri, RouteMap $routeMap): void {
        if ($_ENV['REDIS_ENABLE']){
            try{
                // Rotas estáticas nunca expiram, dinâmicas expiram em 24h
                if ($routeMap->isDinamic){
                    core()->redis()->setex(
                        key: $routeMap->getType().$requestUri,
                        expire: 86400, // 24 horas
                        value: serialize($routeMap)
                    );
                } else {
                    core()->redis()->set(
                        key: $routeMap->getType().$requestUri,
                        value: serialize($routeMap)
                    );
                }
            } catch (\Exception $e){
                static::saveCache($requestUri, $routeMap);
            }
        } else {
            static::saveCache($requestUri, $routeMap);
        }
    }

    private static function saveCache(string $requestUri, RouteMap $routeMap): void {
        $cacheFile = INFRA_PATCH . '/cache/routes/' . $_SERVER['REQUEST_METHOD'] . base64_encode($requestUri) . '.cache';
        file_put_contents($cacheFile, serialize($routeMap), LOCK_EX);
    }

    public static function verifyRouteCache(string $requestUri): RouteMap|null {
        if ($_ENV['REDIS_ENABLE']){
            try{
                $checkHasCache = core()->redis()->get($_SERVER['REQUEST_METHOD'].$requestUri);
                if ($checkHasCache){
                    return unserialize($checkHasCache);
                }
            } catch (\Exception $e){}
        } else {
            $cacheFile = INFRA_PATCH . '/cache/routes/' . $_SERVER['REQUEST_METHOD'] . base64_encode($requestUri) . '.cache';
            if (file_exists($cacheFile)){
                return unserialize(file_get_contents($cacheFile));
            }
        }
        return null;
    }

    // ----------------- Índice de rotas (cache) -----------------
    private static function getRoutesIndexCacheFile(): string {
        return INFRA_PATCH . '/cache/routes/__routes.index.cache';
    }

    private static function loadRoutesIndex(array $controllers): ?array {
        $file = self::getRoutesIndexCacheFile();
        if (!is_file($file)) return null;

        $raw = @file_get_contents($file);
        if ($raw === false) return null;

        $data = @unserialize($raw);
        if (!is_array($data)) return null;

        $savedControllers = $data['controllers'] ?? null;
        $routes = $data['routes'] ?? null;

        if (!is_array($savedControllers) || !is_array($routes)) return null;

        // Compara conjunto de controllers
        $incoming = array_values(array_map('strval', $controllers));
        sort($incoming);
        $saved = array_keys($savedControllers);
        sort($saved);
        if ($incoming !== $saved) return null;

        // Valida mtime dos arquivos
        foreach ($savedControllers as $cls => $info) {
            $f = $info['file'] ?? null;
            $m = $info['mtime'] ?? null;
            if (!$f || !is_file($f)) return null;
            if ((int)@filemtime($f) !== (int)$m) return null;
        }

        return $routes;
    }

    private static function saveRoutesIndex(array $controllersMeta, array $routes): void {
        $file = self::getRoutesIndexCacheFile();
        $payload = [
            'controllers' => $controllersMeta,
            'routes' => $routes,
            'php' => PHP_VERSION_ID,
            'timestamp' => time()
        ];
        @file_put_contents($file, serialize($payload), LOCK_EX);
    }

}