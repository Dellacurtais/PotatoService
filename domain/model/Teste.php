<?php
namespace domain\model;

use infrastructure\core\attributes\validation\MinLength;
use infrastructure\core\database\attributes\Column;
use infrastructure\core\database\EntityModel;

class Teste extends EntityModel {
    public $timestamps = false;

    #[Column(name: "id", primaryKey: true)]
    public $id;

    #[MinLength(4)]
    #[Column(name: "nome")]
    public $nome;

}