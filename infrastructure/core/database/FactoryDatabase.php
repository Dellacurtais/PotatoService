<?php

namespace infrastructure\core\database;


class FactoryDatabase {

    public static function setRepository($classCalled, Repository &$repository){
        $doReflection = new \ReflectionClass($classCalled);
        $attribute = current($doReflection->getAttributes(attributes\SetRepository::class, \ReflectionAttribute::IS_INSTANCEOF));
        $setRepository = $attribute->newInstance();
        if ($setRepository instanceof attributes\SetRepository){
            $Entity = $setRepository->entity;
            $repository->setModel(new $Entity());
        }
    }

}