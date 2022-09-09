<?php

namespace infrastructure\core\general;

use infrastructure\core\interfaces\iValidation;

abstract class MapRequest {

    public function __construct(){
        $Reflection = new \ReflectionClass(get_called_class());
        $AllProperties = $Reflection->getProperties();
        foreach ($AllProperties as $property) {
            $Attributes = $property->getAttributes();
            $propertyName = $property->getName();
            $propertyValue = request()->$propertyName ?? null;
            $this->$propertyName =  $propertyValue;
            foreach ($Attributes as $attribute){
                $attrInstance = $attribute->newInstance();
                if ($attrInstance instanceof iValidation){
                    $attrInstance->validate($propertyName, $propertyValue);
                }
            }
        }
    }

}