<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class Max implements iValidation {

    public function __construct(protected int $max){}

    public function validate($key, $value){
        if ($value > $this->max){
            throw new BusinessException(sprintf(_("O campo %s não pode maior que %d"), _($key), $this->max));
        }
    }
}