<?php
namespace infrastructure\core\database\interfaces;

use infrastructure\core\database\EntityModel;

interface iRepository {

    public function getAll();
    public function findById(int $id);
    public function deleteById(int $id);
    public function save(EntityModel $entity);

}