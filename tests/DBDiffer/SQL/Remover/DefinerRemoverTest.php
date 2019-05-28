<?php
/* vim: set ts=4 sw=4 tw=0 et :*/

namespace jach\DBDiffer\SQL\Remover;

use PHPUnit\Framework\TestCase;

class DefinerRemoverTest extends TestCase
{
    public function testRemovesAutoIncrementPartOfCreateStatement()
    {
        $sql = "CREATE OR REPLACE DEFINER=`root`@`localhost` VIEW `foo` AS select `bar`.`id` AS `id`,`bar`.`type` AS `type` from `bar`;";
        $expectedSql = "CREATE OR REPLACE VIEW `foo` AS select `bar`.`id` AS `id`,`bar`.`type` AS `type` from `bar`;";

        $remover = new DefinerRemover();

        $this->assertSame($expectedSql, $remover->remove($sql));
    }
}
