<?php
namespace domain\service;

use domain\model\Teste;
use infrastructure\core\general\Services;

class TesteService extends Services {

    public function execute(){

        $newTeste = new Teste();
        $newTeste->nome = true;


    }


}