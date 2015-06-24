<?php

namespace jach\DBDiffer;

class SQLTable
{
    private $name;
    private $statement;

    public function __construct($name, $statement)
    {
        $this->name      = $name;
        $this->statement = $statement;
    }

    public function getStatement()
    {
        return $this->statement;
    }
}
