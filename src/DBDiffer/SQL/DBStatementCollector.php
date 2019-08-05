<?php

namespace jach\DBdiffer\SQL;

class DBStatementCollector
{
    private $_createStmtDirPath;

    public function __construct($createStmtDirPath)
    {
        $this->_createStmtDirPath = $createStmtDirPath;
    }

    public function collect()
    {
        $createStmtDir = new \DirectoryIterator($this->_createStmtDirPath);
        $stmtArr = [];
        foreach ($createStmtDir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $createStmt = file_get_contents($fileinfo->getRealPath());
                $createStmt = (new \jach\DBDiffer\SQL\Remover\DefinerRemover())->remove($createStmt);
                $stmtArr[$fileinfo->getFilename()] = $createStmt;
            }
        }
        ksort($stmtArr);
        return $stmtArr;
    }
}
