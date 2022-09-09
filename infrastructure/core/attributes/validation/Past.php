<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class Past implements iValidation {

    public function __construct(protected string|null $format = null){}

    public function validate($key, $value) {
        if ($this->format != null){
            $isDate = \DateTime::createFromFormat($this->format, $value);
        }else{
            $isDate = new \DateTime($value);
        }

        if ($isDate->getTimestamp() > time()){
            throw new BusinessException(sprintf(_("O campo %s deve ser uma data no passado"), _($key)));
        }
    }

}