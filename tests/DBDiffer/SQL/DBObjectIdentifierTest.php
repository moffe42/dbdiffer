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
        $objIdRes = $this->objIdentifier->fromCreateStmt($sql);
        $this->assertSame('TABLE', $objIdRes->objType);
        $this->assertSame('sent_emails', $objIdRes->objName);
    }

    public function testIdentifyViewObjFromCreateStmt()
    {
        $sql = "CREATE OR REPLACE VIEW `folders` AS select `groups`.`id` AS `id`,`groups`.`type` AS `type`,`groups`.`parent` AS `parent`,`groups`.`name` AS `name`,`groups`.`company_id` AS `company_id`,`groups`.`created` AS `created`,`parse_groups_path`(`groups`.`path`) AS `path` from `groups`;";
        $objIdRes = $this->objIdentifier->fromCreateStmt($sql);
        $this->assertSame('VIEW', $objIdRes->objType);
        $this->assertSame('folders', $objIdRes->objName);
    }

    public function testIdentifyFunctionObjFromCreateStmt()
    {
        $sql = "CREATE FUNCTION `foobar`(subpath BLOB) RETURNS text CHARSET latin1 DETERMINISTIC
            BEGIN
                DECLARE foo TEXT;
                SET foo = 'bar';
                RETURN foo;
            END";
        $objIdRes = $this->objIdentifier->fromCreateStmt($sql);
        $this->assertSame('FUNCTION', $objIdRes->objType);
        $this->assertSame('foobar', $objIdRes->objName);
    }

    public function testIdentifyNothingObjFromCreateStmt()
    {
        $sql = "DROP FUNCTION IF EXISTS foobar;";
        $objIdRes = $this->objIdentifier->fromCreateStmt($sql);
        $this->assertSame('', $objIdRes->objType);
        $this->assertSame('', $objIdRes->objName);
    }
}
