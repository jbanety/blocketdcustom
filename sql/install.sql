CREATE TABLE IF NOT EXISTS `#__etd_custom` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `hook` varchar(50) NOT NULL,
  `etdhook` varchar(50) DEFAULT NULL,
  `published` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `exceptions` varchar(255) NOT NULL,
  `access` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `showtitle` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `css` varchar(255) NOT NULL,
  `params` text NOT NULL,
  `ordering` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `hook` (`hook`),
  KEY `idx_hook_ordering` (`hook`, `ordering`),
  KEY `idx_published_hook_ordering` (`published`, `hook`, `ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__etd_custom_lang` (
  `id_custom` int(10) UNSIGNED NOT NULL,
  `id_lang` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id_custom`,`id_lang`),
  KEY `id_custom` (`id_custom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__etd_custom_shop` (
  `id_custom` int(10) NOT NULL,
  `id_shop` int(10) NOT NULL,
  PRIMARY KEY (`id_custom`,`id_shop`),
  KEY `id_custom` (`id_custom`),
  KEY `id_shop` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;