<?php
namespace infrastructure\core\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class BaseRoute {

    public function __construct(public string $basePattern) {}

}