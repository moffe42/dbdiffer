<?php

namespace jach\DBDiffer\SQL\Remover;

use jach\DBDiffer\SQL\Remover as Remover;

class AutoIncrementRemover implements Remover
{
    public function remove($sql)
    {
        return preg_replace('/AUTO_INCREMENT=\d+ /i', '', $sql);
    }
}
