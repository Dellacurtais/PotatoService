<?php

namespace domain\model;

use infrastructure\core\database\attributes\Column;
use infrastructure\core\database\attributes\Entity;
use infrastructure\core\database\EntityModel;

#[Entity(tableName: 'teste', properties: ['timestamps' => false])]
class Teste extends EntityModel {

    #[Column(name: "id", primaryKey: true)]
    public $id;

    #[Column(name: "nome")]
    public $nome;

}