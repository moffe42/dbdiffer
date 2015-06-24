<?php

namespace jach\DBDiffer\SQL\Remover;

class AutoIncrementRemoverTest extends \PHPUnit_Framework_TestCase
{
    public function testRemovesAutoIncrementPartOfCreateStatement()
    {
      $sql = "CREATE TABLE IF NOT EXISTS `users_groups_junction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `access_level` int(11) NOT NULL DEFAULT '0',
  `upload` tinyint(1) NOT NULL DEFAULT '0',
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `added_by` int(11) NOT NULL COMMENT 'User id of the user who added the user to the group',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_group` (`user_id`,`group_id`),
  UNIQUE KEY `group_user` (`group_id`,`user_id`),
  KEY `user_upload` (`user_id`,`upload`),
  KEY `user_access_level` (`user_id`,`access_level`)
) ENGINE=InnoDB AUTO_INCREMENT=3232 DEFAULT CHARSET=utf8
";

        $expectedSql = "CREATE TABLE IF NOT EXISTS `users_groups_junction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `access_level` int(11) NOT NULL DEFAULT '0',
  `upload` tinyint(1) NOT NULL DEFAULT '0',
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `added_by` int(11) NOT NULL COMMENT 'User id of the user who added the user to the group',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_group` (`user_id`,`group_id`),
  UNIQUE KEY `group_user` (`group_id`,`user_id`),
  KEY `user_upload` (`user_id`,`upload`),
  KEY `user_access_level` (`user_id`,`access_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

        $remover = new AutoIncrementRemover();

        $this->assertSame($expectedSql, $remover->remove($sql));
    }
}
