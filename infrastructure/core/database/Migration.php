<?php
namespace infrastructure\core\database;

use Illuminate\Database\Capsule\Manager;

class Migration {

    const FILE_CACHE = INFRA_PATCH . "/migrations/cache.json";
    const MIGRATION_PATH = INFRA_PATCH . '/migrations/*.sql';

    private \PDO $pdo;

    public function __construct(){
        $this->pdo = Manager::connection()->getPdo();
        $this->migrate();
    }

    /**
     * Execute migrations from SQL files.
     */
    protected function migrate(): void {
        $temp_files = glob(self::MIGRATION_PATH);
        $cacheMigration = $this->getCache();

        foreach ($temp_files as $file){
            if (!in_array($file, $cacheMigration)){
                $this->sqlImport($file);
                $cacheMigration[$file] = true;
                $this->updateCache($cacheMigration);
            }
        }
    }

    /**
     * Fetch the cache.
     *
     * @return array
     */
    protected function getCache(): array {
        if (file_exists(self::FILE_CACHE)){
            return json_decode(file_get_contents(self::FILE_CACHE), true);
        }
        return [];
    }

    /**
     * Update the cache file.
     *
     * @param array $cacheMigration
     */
    protected function updateCache(array $cacheMigration): void {
        file_put_contents(self::FILE_CACHE, json_encode($cacheMigration));
    }

    /**
     * Import a SQL file and run its queries.
     *
     * @param string $file The path to the SQL file.
     */
    protected function sqlImport(string $file): void {


        // Iniciar transação
        $this->pdo->beginTransaction();
        try {
            $delimiter = ';';
            $file = fopen($file, 'r');
            $isFirstRow = true;
            $isMultiLineComment = false;
            $sql = '';

            while (!feof($file)) {
                $row = fgets($file);
                if ($isFirstRow) {
                    $row = preg_replace('/^\x{EF}\x{BB}\x{BF}/', '', $row);
                    $isFirstRow = false;
                }
                if (trim($row) == '' || preg_match('/^\s*(#|--\s)/sUi', $row)) {
                    continue;
                }
                $row = trim($this->clearSQL($row, $isMultiLineComment));
                if (preg_match('/^DELIMITER\s+[^ ]+/sUi', $row)) {
                    $delimiter = preg_replace('/^DELIMITER\s+([^ ]+)$/sUi', '$1', $row);
                    continue;
                }
                $offset = 0;
                while (strpos($row, $delimiter, $offset) !== false) {
                    $delimiterOffset = strpos($row, $delimiter, $offset);
                    if ($this->isQuoted($delimiterOffset, $row)) {
                        $offset = $delimiterOffset + strlen($delimiter);
                    } else {
                        $sql = trim($sql . ' ' . trim(substr($row, 0, $delimiterOffset)));
                        $this->query($sql);

                        $row = substr($row, $delimiterOffset + strlen($delimiter));
                        $offset = 0;
                        $sql = '';
                    }
                }
                $sql = trim($sql . ' ' . $row);
            }
            if (strlen($sql) > 0) {
                $this->query($row);
            }
            fclose($file);
        }catch (\PDOException $e) {
            $this->pdo->rollback();
            throw new \Exception("Migration failed. Error: " . $e->getMessage());
        }
    }

    /**
     * Clean a SQL string, removing comments and unnecessary spaces.
     *
     * @param string $sql The SQL string to be cleared.
     * @param bool &$isMultiComment Flag to track multiline comments.
     * @return string The cleaned SQL string.
     */
    protected function clearSQL(string $sql, bool &$isMultiComment): string {
        if ($isMultiComment) {
            if (preg_match('#\*/#sUi', $sql)) {
                $sql = preg_replace('#^.*\*/\s*#sUi', '', $sql);
                $isMultiComment = false;
            } else {
                $sql = '';
            }
            if(trim($sql) == ''){
                return $sql;
            }
        }

        $offset = 0;
        while (preg_match('{--\s|#|/\*[^!]}sUi', $sql, $matched, PREG_OFFSET_CAPTURE, $offset)) {
            list($comment, $foundOn) = $matched[0];
            if ($this->isQuoted($foundOn, $sql)) {
                $offset = $foundOn + strlen($comment);
            } else {
                if (substr($comment, 0, 2) == '/*') {
                    $closedOn = strpos($sql, '*/', $foundOn);
                    if ($closedOn !== false) {
                        $sql = substr($sql, 0, $foundOn) . substr($sql, $closedOn + 2);
                    } else {
                        $sql = substr($sql, 0, $foundOn);
                        $isMultiComment = true;
                    }
                } else {
                    $sql = substr($sql, 0, $foundOn);
                    break;
                }
            }
        }
        return $sql;
    }

    /**
     * Check if a given offset position within a text is inside a quoted string.
     *
     * @param int $offset The position to check.
     * @param string $text The text where the position is checked.
     * @return bool True if the position is quoted, false otherwise.
     */
    protected function isQuoted(int $offset, string $text): bool{
        if ($offset > strlen($text))
            $offset = strlen($text);

        $isQuoted = false;
        for ($i = 0; $i < $offset; $i++) {
            if ($text[$i] == "'")
                $isQuoted = !$isQuoted;
            if ($text[$i] == "\\" && $isQuoted)
                $i++;
        }
        return $isQuoted;
    }

    /**
     * Run a given SQL query using the PDO connection.
     *
     * @param string $sql The SQL string to be executed.
     */
    protected function query(string $sql): void {
        $this->pdo->query($sql)->execute();
    }

}