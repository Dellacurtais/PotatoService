<?php

namespace infrastructure\core\database;

use Illuminate\Database\Eloquent\Collection;
use infrastructure\core\database\attributes\Entity;
use infrastructure\core\database\interfaces\iRepository;
use infrastructure\core\exception\EntityNotFoundOnModelException;
use infrastructure\core\exception\RepositoryEntityNotFoundException;

class Repository implements iRepository {

    public static array $instance = [];

    public EntityModel|null $model = null;

    public function getAll() : Collection {
        return $this->model->all();
    }

    public function findById(int $id) : EntityModel {
        return $this->model->find($id);
    }

    public function deleteById(int $id){

    }

    public function save(mixed $entity, $options = []){
        $this->setModel($entity);

        $this->model->validate();
        $this->model->save($options);
    }


    public static function getInstance() : Repository {
        $classCalled = get_called_class();

        if (!isset(self::$instance[$classCalled])){
            self::$instance[$classCalled] = new $classCalled();
            FactoryDatabase::setRepository(get_called_class(), self::$instance[$classCalled]);
            if (self::$instance[$classCalled]->model == null)
                throw new RepositoryEntityNotFoundException();

        }
        return self::$instance[$classCalled];
    }

    public function setModel($model){
        $this->model = $model;
        $this->model->execute();
        $this->doReflection();
    }

    private function doReflection(){
        $doReflection = new \ReflectionClass($this->model);
        $attribute = current($doReflection->getAttributes(attributes\Entity::class, \ReflectionAttribute::IS_INSTANCEOF));
        if ($attribute){
            $instance = $attribute->newInstance();
            if ($instance instanceof Entity){
                $instance->execute($this->model);
            }
        }else{
            throw new EntityNotFoundOnModelException(sprintf(_("A classe %s não possúi o atributo Entity"), _($this->model::class)));
        }
    }
}