<?php

namespace jach\DBdiffer\SQL;

final class DBObjectDifferResult
{
    public $success;
    public $message;
    public $objType;
    public $objName;
    public $diffResult;

    public function __construct(
        bool $success,
        string $message = '',
        string $objType = '',
        string $objName = '',
        string $diffResult = ''
    ) { 
        $this->success = $success;
        $this->message = $message;
        $this->objType = $objType;
        $this->objName = $objName;
        $this->diffResult = $diffResult;
    }
}
