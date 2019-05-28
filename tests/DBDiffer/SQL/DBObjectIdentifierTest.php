<?php

namespace jach\DBDiffer\SQL;

use PHPUnit\Framework\TestCase;

class DBObjectIdentifierTest extends TestCase
{
    protected $objIdentifier;

    protected function setUp(): void
    {
        $this->objIdentifier = new DBObjectIdentifier();
    }

    public function testIdentifyTableObjFromCreateStmt()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `sent_emails` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=318488 DEFAULT CHARSET=utf8";
        [$objType, $objName] = $this->objIdentifier->fromCreateStmt($sql);
        $this->assertSame('TABLE', $objType);
        $this->assertSame('sent_emails', $objName);
    }

    public function testIdentifyViewObjFromCreateStmt()
    {
        $sql = "CREATE OR REPLACE VIEW `folders` AS select `groups`.`id` AS `id`,`groups`.`type` AS `type`,`groups`.`parent` AS `parent`,`groups`.`name` AS `name`,`groups`.`company_id` AS `company_id`,`groups`.`created` AS `created`,`parse_groups_path`(`groups`.`path`) AS `path` from `groups`;";
        [$objType, $objName] = $this->objIdentifier->fromCreateStmt($sql);
        $this->assertSame('VIEW', $objType);
        $this->assertSame('folders', $objName);
    }

    public function testIdentifyFunctionObjFromCreateStmt()
    {
        $sql = "CREATE FUNCTION `foobar`(subpath BLOB) RETURNS text CHARSET latin1 DETERMINISTIC
            BEGIN
                DECLARE foo TEXT;
                SET foo = 'bar';
                RETURN foo;
            END";
        [$objType, $objName] = $this->objIdentifier->fromCreateStmt($sql);
        $this->assertSame('FUNCTION', $objType);
        $this->assertSame('foobar', $objName);
    }

    public function testIdentifyNothingObjFromCreateStmt()
    {
        $sql = "DROP FUNCTION IF EXISTS foobar;";
        [$objType, $objName] = $this->objIdentifier->fromCreateStmt($sql);
        $this->assertSame('', $objType);
        $this->assertSame('', $objName);
    }
}
