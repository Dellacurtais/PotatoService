<?php

namespace infrastructure\core\database\attributes;

use Attribute;

#[attribute(Attribute::TARGET_CLASS)]
class SetRepository {

    public function __construct(public mixed $entity){}

}