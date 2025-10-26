<?php

namespace infrastructure\core\general;

use infrastructure\core\interfaces\iValidation;

abstract class MapRequest {

    public function __construct(){
        $meta = ReflectionCache::get(get_called_class());
        foreach ($meta['properties'] as $propertyName => $attributesMeta) {
            $propertyValue = request()->$propertyName ?? null;
            $this->$propertyName =  $propertyValue;
            foreach ($attributesMeta as $attrMeta){
                $attrClass = $attrMeta['class'];
                $args = $attrMeta['args'] ?? [];
                // Instantiate attribute without reflecting target class again
                $attrInstance = new $attrClass(...$args);
                if ($attrInstance instanceof iValidation){
                    $attrInstance->validate($propertyName, $propertyValue);
                }
            }
        }
    }

    public function validateAttrs(): void {
        $meta = ReflectionCache::get(get_called_class());
        foreach ($meta['properties'] as $propertyName => $attributesMeta) {
            foreach ($attributesMeta as $attrMeta){
                $attrClass = $attrMeta['class'];
                $args = $attrMeta['args'] ?? [];
                $attrInstance = new $attrClass(...$args);
                if ($attrInstance instanceof iValidation){
                    $attrInstance->validate($propertyName, $this->$propertyName);
                }
            }
            if ($this->$propertyName instanceof MapRequest){
                $this->$propertyName->validateAttrs();
            }
        }
    }

}