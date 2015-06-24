<?php

namespace jach\DBDiffer;

interface SQLTableFetcher
{
    public function fetch($tableName);
}
