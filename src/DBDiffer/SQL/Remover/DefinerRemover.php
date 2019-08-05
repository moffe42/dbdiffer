<?php

namespace jach\DBDiffer\SQL\Remover;

use jach\DBDiffer\SQL\Remover as Remover;

class DefinerRemover implements Remover
{
    public function remove($sql)
    {
        return preg_replace('/DEFINER=\S* ?/i', '', $sql);
    }
}
