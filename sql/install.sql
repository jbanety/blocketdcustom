CREATE TABLE IF NOT EXISTS `#__etd_custom` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `hook` varchar(50) NOT NULL,
  `etdhook` varchar(50) DEFAULT NULL,
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `showtitle` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `css` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hook` (`hook`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__etd_custom_lang` (
  `id_custom` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id_custom`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__etd_custom_shop` (
  `id_custom` int(10) NOT NULL,
  `id_shop` int(10) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(10) NOT NULL,
  `exceptions` text NOT NULL,
  PRIMARY KEY (`id_custom`,`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
