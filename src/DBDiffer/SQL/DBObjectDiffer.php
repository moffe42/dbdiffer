<?php

namespace jach\DBDiffer\SQL;

class DBObjectDiffer
{
    private $DB1;
    private $DB1Name;
    private $DB2;
    private $DB2Name;

    private $objIdentifier;
    private $differ;

    public function __construct($DB1, $DB2)
    {
        $this->DB1 = $DB1;
        $this->DB1Name = $DB1->query('SELECT database()')->fetchColumn();
        $this->DB2 = $DB2;
        $this->DB2Name = $DB2->query('SELECT database()')->fetchColumn();

        $this->objIdentifier = new \jach\DBDiffer\SQL\DBObjectIdentifier();
        $this->differ = new \SebastianBergmann\Diff\Differ('--- ' . $this->DB1Name . '\n+++ ' . $this->DB2Name . '\n', false);
    }

    private function _fetchCreateStmt(\PDO $DB, \jach\DBDiffer\SQL\DBObjectIdentificationResult $dbObjIdRes): ?string
    {
        $stmt = $DB->query('SHOW CREATE ' . $dbObjIdRes->objType . ' `' . $dbObjIdRes->objName . '`');
        if (!$stmt) {
            return null;
        }
        return $stmt->fetch(\PDO::FETCH_ASSOC)['Create ' . ucfirst(strtolower($dbObjIdRes->objType))];
    }

    private function _cleanseCreateStmt(string $createStmt): string
    {
        $createStmt = (new \jach\DBDiffer\SQL\Remover\AutoIncrementRemover())->remove($createStmt);
        $createStmt = (new \jach\DBDiffer\SQL\Remover\DefinerRemover())->remove($createStmt);
        $createStmt = trim($createStmt);
        return $createStmt;
    }

    public function diff(string $userProvidetCreateStmt): \jach\DBDiffer\SQL\DBObjectDifferResult
    {
        try {
            $dbObjIdRes = $this->objIdentifier->fromCreateStmt($userProvidetCreateStmt);
        } catch (\RuntimeException $e) {
            return (new \jach\DBDiffer\SQL\DBObjectDifferResult(false, $e->getMessage()));
        }

        if ($dbObjIdRes->objType === '' || $dbObjIdRes->objName == '') {
            return (new \jach\DBDiffer\SQL\DBObjectDifferResult(true, 'There is nothing to compare i.e. no difference.'));
        }

        $stmtArr = [];
        foreach ([
            $this->DB1Name => $this->DB1,
            $this->DB2Name => $this->DB2
        ] as $DBName => $DB) {
            $stmt = $this->_fetchCreateStmt($DB, $dbObjIdRes);
            if (is_null($stmt)) {
                return (new \jach\DBDiffer\SQL\DBObjectDifferResult(
                    false,
                    'In database ' . $DBName . ' object ' . $dbObjIdRes->objType . ' `' . $dbObjIdRes->objName . '` does not exists',
                    $dbObjIdRes->objType,
                    $dbObjIdRes->objName
                ));
            }
            $stmt = $this->_cleanseCreateStmt($stmt);
            $stmtArr[] = $stmt;
        }

        [$stmt1, $stmt2] = $stmtArr;
        if ($stmt1 == $stmt2) {
            return (new \jach\DBDiffer\SQL\DBObjectDifferResult(true));
        }

        return (new \jach\DBDiffer\SQL\DBObjectDifferResult(
            false,
            $dbObjIdRes->objType . ' ' . $dbObjIdRes->objName . ' is not equal',
            $dbObjIdRes->objType,
            $dbObjIdRes->objName,
            $this->differ->diff($stmt1, $stmt2)
        ));
    }
}
