-- $Id: install.mysql.utf8.sql 60 2010-11-27 18:45:40Z chdemko $

DROP TABLE IF EXISTS `#__workflowservice_categories`;
 
CREATE TABLE `#__workflowservice_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(256) NOT NULL,
  `state` int(3) NOT NULL DEFAULT '1',
  `workflows` TEXT,
  `created` DATETIME NOT NULL,
  `ordering` int(3) NOT NULL DEFAULT '1',
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
 
INSERT INTO `#__workflowservice_categories` (`category`, `ordering`, `state`) VALUES
        ('Hidden', 1, 1);