CREATE TABLE `session` (
  `id` varchar(32) NOT NULL COMMENT 'The PHP session ID',
  `address` int(10) unsigned NOT NULL default '0' COMMENT 'IP address stored as a unsigned long',
  `access` int(10) NOT NULL default '0' COMMENT 'PHP session access time in UNIX format',
  `data` text COMMENT 'PHP session data',
  PRIMARY KEY  (`id`,`address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PHP session data'