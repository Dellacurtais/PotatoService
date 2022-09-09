<?php

namespace infrastructure\core\database\attributes;

use Attribute;

#[attribute(Attribute::TARGET_PROPERTY)]
class Column {

    public function __construct(
        public bool $primaryKey = false,
        public string|null $name = null
    ){}

}