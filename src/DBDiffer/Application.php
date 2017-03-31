<?php

namespace jach\DBDiffer;

use SebastianBergmann\Version;
use Symfony\Component\Console\Application as AbstractApplication;

class Appllication extends AbstractApplication
{
    public function __construct()
    {
        $version = new Version('1.0.0', dirname(dirname(__DIR__)));
        parent::__construct('DBDiffer', $version->getVersion());
    }
}
