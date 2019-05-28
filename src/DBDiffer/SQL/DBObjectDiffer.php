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

    public function __construct($DB1, $DB2) {
        $this->DB1 = $DB1;
        $this->DB1Name = $DB1->query('SELECT database()')->fetchColumn();
        $this->DB2 = $DB2;
        $this->DB2Name = $DB2->query('SELECT database()')->fetchColumn();

        $this->objIdentifier = new \jach\DBDiffer\SQL\DBObjectIdentifier();
        $this->differ = new \SebastianBergmann\Diff\Differ('--- ' . $this->DB1Name . '\n+++ ' . $this->DB2Name . '\n', false);
    }

    private function _fetchCreateStmt(\PDO $DB, string $objType, string $objName): ?string
    {
        $stmt = $DB->query('SHOW CREATE ' . $objType . ' `' . $objName . '`');
        if (!$stmt) {
            return null;
        }
        return $stmt->fetch(\PDO::FETCH_ASSOC)['Create ' . ucfirst(strtolower($objType))];
    }

    private function _cleanseCreateStmt(string $createStmt): string
    {
        $createStmt = (new \jach\DBDiffer\SQL\Remover\AutoIncrementRemover())->remove($createStmt);
        $createStmt = (new \jach\DBDiffer\SQL\Remover\DefinerRemover())->remove($createStmt);
        $createStmt = trim($createStmt);
        return $createStmt;
    }

    public function diff(string $userProvidetCreateStmt): array
    {
        try {
            [$objType, $objName] = $this->objIdentifier->fromCreateStmt($userProvidetCreateStmt);
        } catch (\RuntimeException $e) {
            return [$e->getMessage()];
        }

        if ($objType === '' || $objName == '') {
            return [];
        }

        $stmtArr = [];
        foreach ([
            $this->DB1Name => $this->DB1,
            $this->DB2Name => $this->DB2
        ] as $DBName => $DB) {
            $stmt = $this->_fetchCreateStmt($DB, $objType, $objName);
            if (is_null($stmt)) {
                return ['In database ' . $DBName . ' object ' . $objType . ' `' . $objName . '` does not exists'];
            }
            $stmt = $this->_cleanseCreateStmt($stmt);
            $stmtArr[] = $stmt;
        }

        [$stmt1, $stmt2] = $stmtArr;
        if ($stmt1 == $stmt2) {
            return [];
        }

        return [
            $objType . ' ' . $objName . ' is not equal',
            $this->differ->diff($stmt1, $stmt2),
        ];
    }
}
