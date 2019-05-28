<?php

namespace jach\DBdiffer\SQL;

class DBObjectIdentifier
{
    private function _getStmtParts($sql)
    {
        $sql = preg_replace('/\s?=\s?/', '=', $sql);
        return preg_split('/\s+/', $sql, -1, PREG_SPLIT_NO_EMPTY);
    }

    private function _trimObjNamePart($objNamePart)
    {
        $objName = preg_replace('/\([^)]*\)?/', '', $objNamePart);
        return trim($objName, '`');
    }

    public function fromCreateStmt($sql)
    {
        $sql = (new \jach\DBDiffer\SQL\Remover\IfNotExistsRemover())->remove($sql);
        $parts = $this->_getStmtParts($sql);

        if (strtoupper($parts[0]) !== 'CREATE') {
            return new \jach\DBDiffer\SQL\DBObjectIdentificationResult('', '');
        }

        foreach (['TABLE', 'VIEW', 'FUNCTION'] as $objKeyword) {
            $objKeywordIndex = array_search($objKeyword, array_map('strtoupper', $parts));
            if ($objKeywordIndex !== FALSE) {
                return new \jach\DBDiffer\SQL\DBObjectIdentificationResult(
                    $objKeyword, 
                    $this->_trimObjNamePart($parts[$objKeywordIndex + 1])
                );
            }
        }

        throw new \Exception('Unable to identify database object');
    }
}
