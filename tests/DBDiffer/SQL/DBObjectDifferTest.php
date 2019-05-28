<?php

namespace jach\DBDiffer\SQL;

use PHPUnit\Framework\TestCase;

class DBObjectDifferTest extends TestCase
{
    protected $mockDB1;
    protected $mockDB2;
    protected $dbNameQuery1;
    protected $dbNameQuery2;

    protected function setUp(): void
    {
        $this->dbNameQuery1 = $this->createMock('\PDOStatement');
        $this->dbNameQuery1->method('fetchColumn')->willReturn('local-db');

        $this->dbNameQuery2 = $this->createMock('\PDOStatement');
        $this->dbNameQuery2->method('fetchColumn')->willReturn('remote-db');

        $this->mockDB1 = $this->getMockBuilder('\PDO')
            ->disableOriginalConstructor()
            ->setMethods(['query'])
            ->getMock();


        $this->mockDB2 = $this->getMockBuilder('\PDO')
            ->disableOriginalConstructor()
            ->setMethods(['query'])
            ->getMock();
    }

    public function testDiffingEqualStmt()
    {
        $userprovidetCreateStmt = "CREATE TABLE IF NOT EXISTS `sent_emails` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8";


        $createStmt1 = [
            "Create Table" => "CREATE TABLE `sent_emails` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=318488 DEFAULT CHARSET=utf8",
        ];

        $createStmt2 = [
            "Create Table" => "CREATE TABLE `sent_emails` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8",
        ];

        $dbShowCreateQuery1 = $this->createMock('\PDOStatement');
        $dbShowCreateQuery1->method('fetch')->willReturn($createStmt1);

        $dbShowCreateQuery2 = $this->createMock('\PDOStatement');
        $dbShowCreateQuery2->method('fetch')->willReturn($createStmt2);

        $this->mockDB1->method('query')->will(
            $this->returnValueMap(
                [
                    ['SELECT database()', $this->dbNameQuery1],
                    ['SHOW CREATE TABLE `sent_emails`', $dbShowCreateQuery1]
                ]
            )
        );

        $this->mockDB2->method('query')->will(
            $this->returnValueMap(
                [
                    ['SELECT database()', $this->dbNameQuery2],
                    ['SHOW CREATE TABLE `sent_emails`', $dbShowCreateQuery2]
                ]
            )
        );

        $dbObjDiffer = new DBObjectDiffer($this->mockDB1, $this->mockDB2);
        $this->assertEquals([], $dbObjDiffer->diff($userprovidetCreateStmt));
    }

    public function testDiffingUnequalStmt()
    {
        $userprovidetCreateStmt = "CREATE TABLE IF NOT EXISTS `sent_emails` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8";


        $createStmt1 = [
            "Create Table" => "CREATE TABLE `sent_emails` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `lastname` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=318488 DEFAULT CHARSET=utf8",
        ];

        $createStmt2 = [
            "Create Table" => "CREATE TABLE `sent_emails` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8",
        ];

        $dbShowCreateQuery1 = $this->createMock('\PDOStatement');
        $dbShowCreateQuery1->method('fetch')->willReturn($createStmt1);

        $dbShowCreateQuery2 = $this->createMock('\PDOStatement');
        $dbShowCreateQuery2->method('fetch')->willReturn($createStmt2);

        $this->mockDB1->method('query')->will(
            $this->returnValueMap(
                [
                    ['SELECT database()', $this->dbNameQuery1],
                    ['SHOW CREATE TABLE `sent_emails`', $dbShowCreateQuery1]
                ]
            )
        );

        $this->mockDB2->method('query')->will(
            $this->returnValueMap(
                [
                    ['SELECT database()', $this->dbNameQuery2],
                    ['SHOW CREATE TABLE `sent_emails`', $dbShowCreateQuery2]
                ]
            )
        );

        $dbObjDiffer = new DBObjectDiffer($this->mockDB1, $this->mockDB2);
        $this->assertNotCount(0, $dbObjDiffer->diff($userprovidetCreateStmt));
    }
}
