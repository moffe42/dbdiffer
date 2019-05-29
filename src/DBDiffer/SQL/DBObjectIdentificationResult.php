<?php

namespace jach\DBdiffer\SQL;

final class DBObjectIdentificationResult
{
    public $objType;
    public $objName;

    public function __construct(string $objType, string $objName)
    {
        $this->objType = $objType;
        $this->objName = $objName;
    }
}
