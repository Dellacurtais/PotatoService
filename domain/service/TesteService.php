<?php
namespace domain\service;

use domain\model\Teste;
use infrastructure\core\attributes\Autowired;
use infrastructure\core\attributes\Transactional;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\general\Services;

class TesteService extends Services {

    #[Transactional]
    public function execute(){

        $newTeste = new Teste();
        $newTeste->nome = "Joanes";
        $newTeste->save();

    }


}