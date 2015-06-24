<?php

namespace jach\DBDiffer\SQL\Remover;

class IfNotExistsRemoverTest extends \PHPUnit_Framework_TestCase
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
