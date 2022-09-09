<?php
namespace domain\repository;

use domain\model\Teste;
use infrastructure\core\database\attributes\SetRepository;
use infrastructure\core\database\Repository;

#[SetRepository(entity: Teste::class)]
class TesteRepository extends Repository {


}