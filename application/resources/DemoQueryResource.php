<?php
namespace application\resources;

use application\resources\request\DemoResquest;
use domain\service\TesteService;
use infrastructure\core\attributes\Autowired;
use infrastructure\core\attributes\Cache;
use infrastructure\core\attributes\Route;
use infrastructure\core\attributes\Transactional;
use infrastructure\core\enums\ContentType;
use infrastructure\core\enums\HttpRequest;
use infrastructure\core\enums\StatusCode;

class DemoQueryResource {

    #[Autowired(class: TesteService::class)]
    public TesteService $testeService;

    #[Transactional]
    #[Route(route: 'Home', code: StatusCode::OK, type: HttpRequest::GET, headers: [ ContentType::CONTENT_JSON ])]
    public function exampleRouting(){
        $this->testeService->execute();
    }

    #[Route(route: 'testMapRequest', code: StatusCode::OK, type: HttpRequest::GET, headers: [ ContentType::CONTENT_JSON ])]
    public function exampleWithMapRequest(DemoResquest $demoResquest){
        echo $demoResquest->nome;
        echo $demoResquest->sobrenome;

    }

    #[Cache(time: 900)]
    #[Route(route: 'testCache', code: StatusCode::OK, type: HttpRequest::GET, headers: [ ContentType::CONTENT_JSON ])]
    public function exampleRoutingWithCache(){
        echo "teste";
    }

}