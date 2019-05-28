<?php

namespace jach\DBdiffer\SQL;

class DBManager
{
    private $_DB;

    public function __construct($DB)
    {
        $this->_DB = $DB;
    }

    public function create(array $stmtArr)
    {
        $this->_DB->query('CREATE DATABASE __dbdiffer');
        $this->_DB->query('USE __dbdiffer');
        $this->_DB->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($stmtArr as $fileName => $stmt) {
            if ($this->_DB->query($stmt) == FALSE) {
                throw new \Exception("Unable to execute create statement in {$fileName}");
            }
        }
        $this->_DB->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function drop()
    {
        $this->_DB->query('DROP DATABASE __dbdiffer');
    }
}
