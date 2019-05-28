<?php

namespace jach\DBdiffer\SQL;

class DBCreator
{
    private $_DB;
    private $_stmtArr = [];

    public function __construct($DB, $createStmtDirPath)
    {
        $this->_DB = $DB;

        $createStmtDir = new \DirectoryIterator($createStmtDirPath);
        foreach ($createStmtDir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $createStmt = file_get_contents($fileinfo->getRealPath());
                $createStmt = (new \jach\DBDiffer\SQL\Remover\DefinerRemover())->remove($createStmt);
                $this->_stmtArr[$fileinfo->getFilename()] = $createStmt;
            }
        }
        ksort($this->_stmtArr);
    }

    public function getStmtArr()
    {
        return $this->_stmtArr;
    }

    public function create()
    {
        $this->_DB->query('CREATE DATABASE __dbdiffer');
        $this->_DB->query('USE __dbdiffer');
        $this->_DB->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($this->_stmtArr as $fileName => $stmt) {
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
