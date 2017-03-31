<?php
/* vim: set ts=4 sw=4 tw=0 et :*/

namespace jach\DBDiffer\SQL\Remover;

use PHPUnit\Framework\TestCase;

class IfNotExistsRemoverTest extends TestCase
{
    public function testRemovesIfNotExistsPartOfCreateStatement()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `sent_emails` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=318488 DEFAULT CHARSET=utf8";

        $expectedSql = "CREATE TABLE `sent_emails` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=318488 DEFAULT CHARSET=utf8";

        $remover = new IfNotExistsRemover();

        $this->assertSame($expectedSql, $remover->remove($sql));
    }
}
