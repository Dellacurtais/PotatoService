<?php
namespace domain\service;

use domain\model\Teste;
use domain\repository\TesteRepository;
use infrastructure\core\attributes\Autowired;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\general\Services;

class TesteService extends Services {


    #[Autowired(class: TesteRepository::class)]
    public TesteRepository $testeRepository;


    public function execute(){

        $newTeste = new Teste();
        $newTeste->nome = true;

        $this->testeRepository->save($newTeste);

        //throw new BusinessException("Rolback");

    }


}