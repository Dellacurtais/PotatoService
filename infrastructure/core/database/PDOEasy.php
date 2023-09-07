<?php
namespace infrastructure\core\database;

use Illuminate\Database\Capsule\Manager;

/**
 * PDOEasy Class
 * A simple PDO wrapper for basic database operations.
 */
class PDOEasy {

    /**
     * The PDO instance.
     *
     * @var \PDO
     */
    public \PDO $db;

    /**
     * Constructor.
     * Initializes the PDO instance.
     */
    public function __construct(){
        $this->db = Manager::connection()->getPdo();
    }

    /**
     * Count the number of rows from a given SQL.
     *
     * @param string $sql The SQL string.
     * @return int The number of rows.
     */
    protected function count(string $sql): int {
        return $this->db->query($sql)->fetchColumn();
    }

    /**
     * Run a given SQL query.
     *
     * @param string $sql The SQL string.
     * @param array $bind The binding parameters.
     * @return \PDOStatement The resulting statement.
     * @throws \PDOException If there's a database error.
     */
    protected function run(string $sql, array $bind=[]): \PDOStatement {
        $sql = trim($sql);
        try {
            $result = $this->db->prepare($sql);
            $result->execute($bind);
            return $result;
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Fetch all rows from a given SQL query.
     *
     * @param string $sql The SQL string.
     * @param array $bind The binding parameters.
     * @return array The fetched rows.
     */
    protected function get(string $sql, array $bind=[]): array {
        $result = $this->run($sql, $bind);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $result->fetchAll();
    }

    /**
     * Fetch the first row from a given SQL query.
     *
     * @param string $sql The SQL string.
     * @param array $bind The binding parameters.
     * @return array|null The fetched row or null if not found.
     */
    protected function getFirst(string $sql, array $bind=[]): ?array {
        $result = $this->run($sql, $bind);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $result->fetch();
    }

    /**
     * Insert data into a table.
     *
     * @param string $table The table name.
     * @param array $data The data to insert.
     * @return false|string The ID of the last inserted row.
     */
    protected function create(string $table, array $data): false|string {
        $fields = $this->filter($table, $data);
        $sql = "INSERT INTO " . $table . " (" . implode(", ", $fields) . ") VALUES (:" . implode(", :", $fields) . ");";
        $bind = array();
        foreach($fields as $field)
            $bind[":$field"] = $data[$field];

        $result = $this->run($sql, $bind);

        return $this->db->lastInsertId();
    }

    /**
     * Read rows from a table.
     *
     * @param string $table The table name.
     * @param string $where The WHERE condition.
     * @param array $bind The binding parameters.
     * @param string $fields The fields to select.
     * @param int|null $limit Limit the number of rows.
     * @return array The fetched rows.
     */
    protected function read(string $table, string $where="", array $bind=[], string $fields="*", ?int $limit=null): array {
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

    /**
     * Update rows in a table.
     *
     * @param string $table The table name.
     * @param array $data The data to update.
     * @param string $where The WHERE condition.
     * @param array $bind The binding parameters.
     * @return int The number of affected rows.
     */
    protected function update(string $table, array $data, string $where, array $bind=[]): int {
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

    /**
     * Delete rows from a table.
     *
     * @param string $table The table name.
     * @param string $where The WHERE condition.
     * @param array $bind The binding parameters.
     * @return int The number of deleted rows.
     */
    protected function delete(string $table, string $where, array $bind=[]): int {
        $sql = "DELETE FROM " . $table . " WHERE " . $where . ";";
        $result = $this->run($sql, $bind);
        return $result->rowCount();
    }

    /**
     * Fetch all table names from the database.
     *
     * @return array The table names.
     */
    protected function getTables(): array {
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

    /**
     * Fetch column details from a table.
     *
     * @param string $table The table name.
     * @return array The column details.
     */
    protected function getColumns(string $table): array {
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

    /**
     * Filter out data fields that do not match table columns.
     *
     * @param string $table The table name.
     * @param array $data The data to filter.
     * @return array The filtered data keys.
     */
    private function filter(string $table, array $data): array {
        $sql = "DESCRIBE " . $table . ";";
        $key = "Field";
        if(false !== ($list = $this->run($sql))) {
            $fields = array();
            foreach($list as $record)
                $fields[] = $record[$key];
            return array_values(array_intersect($fields, array_keys($data)));
        }
        return [];
    }

}