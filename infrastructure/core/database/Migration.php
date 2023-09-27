<?php
namespace infrastructure\core\database;

use Illuminate\Database\Capsule\Manager;
use infrastructure\core\exception\BusinessException;

class Migration {

    const FILE_CACHE = INFRA_PATCH . "/migrations/cache.json";
    const MIGRATION_PATH = INFRA_PATCH . '/migrations/*.sql';

    /**
     * Execute migrations from SQL files.
     */
    public function migrate(): void {
        $temp_files = glob(self::MIGRATION_PATH);
        $cacheMigration = $this->getCache();

        foreach ($temp_files as $file){
            $baseName = basename($file);
            if (!in_array($baseName, $cacheMigration)){
                logInfo("Init File: ".$baseName);
                $this->sqlImport($file);
                $cacheMigration[$baseName] = true;
                $this->updateCache($cacheMigration);
                logInfo("Finish File: ".$baseName);
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
        Manager::connection()->beginTransaction();
        if (Manager::connection()->transactionLevel()){
            echo "Level: ".Manager::connection()->transactionLevel();
        }
        try {
            foreach($this->parseSQLFile($file) as $command){
                Manager::connection()->statement($command);
            }
            Manager::connection()->commit();
        } catch (\Exception $e) {
            Manager::connection()->rollBack();
            throw new BusinessException("Migration failed. Error: " . $e->getMessage());
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
        Manager::connection()->statement($sql);
    }

    function parseSQLFile($filePath) {
        $content = file_get_contents($filePath);
        $length = strlen($content);

        $commands = [];
        $currentCommand = "";
        $isInsideString = false;
        $isInsideComment = false;
        $currentChar = '';
        $prevChar = '';
        for ($i = 0; $i < $length; $i++) {
            $prevChar = $currentChar;
            $currentChar = $content[$i];

            // Check for single-line comment
            if ($currentChar === '-' && $prevChar === '-' && !$isInsideString) {
                $isInsideComment = true;
            }

            // Check for end of single-line comment
            if ($isInsideComment && ($currentChar === "\n" || $currentChar === "\r")) {
                $isInsideComment = false;
            }

            // Skip content inside comments
            if ($isInsideComment) {
                continue;
            }

            // Check for start of multi-line comment
            if ($currentChar === '*' && $prevChar === '/' && !$isInsideString) {
                $isInsideComment = true;
            }

            // Check for end of multi-line comment
            if ($currentChar === '/' && $prevChar === '*' && $isInsideComment) {
                $isInsideComment = false;
                continue;
            }

            // Check for strings
            if ($currentChar === "'" && !$isInsideComment) {
                $isInsideString = !$isInsideString;
            }

            // Check for end of command
            if ($currentChar === ';' && !$isInsideString && !$isInsideComment) {
                $commands[] = trim($currentCommand . ';');
                $currentCommand = "";
                continue;
            }

            $currentCommand .= $currentChar;
        }

        if (trim($currentCommand) !== '') {
            $commands[] = trim($currentCommand);
        }
        return $commands;
    }

    function startReadOnlyTransaction() {
        $connection = Manager::connection();
        $driver = $connection->getDriverName();

        switch ($driver) {
            case 'mysql':
                $connection->unprepared('SET TRANSACTION READ ONLY');
                break;
            case 'pgsql':
                $connection->unprepared('BEGIN TRANSACTION READ ONLY');
                break;
            case 'sqlite':
                // SQLite não suporta explicitamente transações read-only na mesma
                // forma que outros DBMSs. No entanto, por padrão, transações SQLite são read-only.
                $connection->unprepared('BEGIN');
                break;
            case 'sqlsrv': // Microsoft SQL Server
                // SQL Server não suporta transações read-only no mesmo sentido que outros DBMSs.
                // No entanto, você pode definir o nível de isolamento da transação para SNAPSHOT,
                // o que dá um efeito similar em termos de consistência dos dados lidos.
                $connection->unprepared('SET TRANSACTION ISOLATION LEVEL SNAPSHOT');
                break;
            case 'oci8': // Oracle
                // Oracle suporta transações read-only
                $connection->unprepared('SET TRANSACTION READ ONLY');
                break;
            // @TODO adicionar outros?
            default:
                throw new Exception("DB driver $driver não suportado para transações read-only");
        }

        $connection->beginTransaction();
    }

}