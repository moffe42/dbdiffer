<?php

namespace jach\DBDiffer\SQL\Remover;

use jach\DBDiffer\SQL\Remover as Remover;

class IfNotExistsRemover implements Remover
{
    public function remove($sql)
    {
        return preg_replace('/IF NOT EXISTS /i', '', $sql);
    }
}
