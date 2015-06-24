<?php

namespace jach\DBDiffer;

class SQLDiffer
{
    public function diff($sql1, $sql2)
    {
        return $sql1 === $sql2;
    }
}
