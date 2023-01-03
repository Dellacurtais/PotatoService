<?php

namespace infrastructure\core\database;



use Illuminate\Database\Capsule\Manager;

class PDOEasy {

    public \PDO $db;

    public function __construct(){
        $this->db = Manager::connection()->getPdo();
    }

    protected function count($sql){
        return $this->db->query($sql)->fetchColumn();
    }

    protected function run($sql, $bind=array()) {
        $sql = trim($sql);
        try {
            $result = $this->db->prepare($sql);
            $result->execute($bind);
            return $result;
        } catch (\PDOException $e) {
            echo $e->getMessage(); exit(1);
        }
    }

    protected function get($sql, $bind=array()) {
        $result = $this->run($sql, $bind);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $result->fetchAll();
    }

    protected function getFirst($sql, $bind=array()) {
        $result = $this->run($sql, $bind);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $result->fetch();
    }

    protected function create($table, $data) {
        $fields = $this->filter($table, $data);
        $sql = "INSERT INTO " . $table . " (" . implode(", ", $fields) . ") VALUES (:" . implode(", :", $fields) . ");";
        $bind = array();
        foreach($fields as $field)
            $bind[":$field"] = $data[$field];

        $result = $this->run($sql, $bind);

        return $this->db->lastInsertId();
    }

    protected function read($table, $where="", $bind=array(), $fields="*", $limit = null) {
        $sql = "SELECT " . $fields . " FROM " . $table;
        if(!empty($where))
            $sql .= " WHERE " . $where;

        if (!is_null($limit)){
            $sql .= " LIMIT {$limit} ";
        }

        $sql .= ";";

        $result = $this->run($sql, $bind);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        $rows = array();
        while($row = $result->fetch()) {
            $rows[] = $row;
        }
        return $rows;
    }

    protected function update($table, $data, $where, $bind = array()) {
        $fields = $this->filter($table, $data);
        $fieldSize = sizeof($fields);
        $sql = "UPDATE " . $table . " SET ";
        for($f = 0; $f < $fieldSize; ++$f) {
            if($f > 0)
                $sql .= ", ";
            $sql .= $fields[$f] . " = :update_" . $fields[$f];
        }
        $sql .= " WHERE " . $where . ";";

        foreach($fields as $field)
            $bind[":update_$field"] = $data[$field];


        $result = $this->run($sql, $bind);
        return $result->rowCount();
    }

    protected function delete($table, $where, $bind="") {
        $sql = "DELETE FROM " . $table . " WHERE " . $where . ";";
        $result = $this->run($sql, $bind);
        return $result->rowCount();
    }

    protected function getTables(){
        $sql = "SHOW TABLES";
        $result = $this->run($sql);
        $result->setFetchMode(\PDO::FETCH_NUM);


        $rows = array();
        $row = $result->fetchAll();
        foreach($row as $item){
            $rows[] = $item[0];
        }
        return $rows;
    }

    protected function getColuns($table){
        $sql = "SHOW COLUMNS FROM {$table}";
        $result = $this->run($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);


        $rows = array();
        $row = $result->fetchAll();
        foreach($row as $item){
            $rows[] = $item;
        }
        return $rows;

    }

    protected function filter($table, $data) {
        $sql = "DESCRIBE " . $table . ";";
        $key = "Field";
        if(false !== ($list = $this->run($sql))) {
            $fields = array();
            foreach($list as $record)
                $fields[] = $record[$key];
            return array_values(array_intersect($fields, array_keys($data)));
        }
        return array();
    }

}