<?php

namespace infrastructure\core\database\attributes;

use Attribute;
use infrastructure\core\database\EntityModel;

#[Attribute(Attribute::TARGET_CLASS)]
class Entity {

    public function __construct(
        public string $tableName,
        public array $properties = [],
        public array $args = []){
    }

    public function execute(EntityModel &$entityModel){
        $entityModel->setTable($this->tableName);
        $entityModel->fill($this->args);
        foreach ($this->properties as $property=>$value){
            $entityModel->$property = $value;
        }
    }

}