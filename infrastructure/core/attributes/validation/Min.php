<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class Min implements iValidation {

    public function __construct(protected int $min){}

    public function validate($key, $value){
        if ($value < $this->min){
            throw new BusinessException(sprintf(_("O campo %s não pode menor que %d"), _($key), $this->min));
        }
    }
}