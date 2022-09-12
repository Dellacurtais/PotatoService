<?php

namespace infrastructure\core\database;


use infrastructure\core\exception\SetRepositoryAttributeNotFoundException;

class FactoryDatabase {


    public static function setRepository($classCalled, Repository &$repository){
        $doReflection = new \ReflectionClass($classCalled);
        $attribute = current($doReflection->getAttributes(attributes\SetRepository::class, \ReflectionAttribute::IS_INSTANCEOF));

        if ($attribute){
            $setRepository = $attribute->newInstance();
            if ($setRepository instanceof attributes\SetRepository){
                $Entity = $setRepository->entity;
                $repository->setModel(new $Entity());
            }
        }else{
            throw new SetRepositoryAttributeNotFoundException(sprintf(_("A classe %s não possúi o atributo SetRepository"), _($classCalled)));
        }
    }

}