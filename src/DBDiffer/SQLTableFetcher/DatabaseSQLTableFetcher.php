<?php

namespace jach\DBDiffer\SQLTableFetcher;

use jach\DBDiffer\SQLTableFetcher as Fetcher;

class DatabaseSQLTableFetcher implements Fetcher
{
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function fetch($tableName)
    {
        $sql = "SHOW CREATE TABLE {$tableName}";

        $stmt = $this->db->query($sql);

        if (!$stmt) {
            throw new \RuntimeException("{$tableName} not found");
        }

        $sqlStatement = $stmt->fetch()[1];

        return new \jach\DBDiffer\SQLTable($tableName, $sqlStatement);
    }
}
