<?php

class KingK_BbCodeManager_BbCodeManager
{
	public static function install($addon)
	{
		$db = XenForo_Application::get('db');
		
		if(empty($addon))
		{
			$db->query("CREATE TABLE IF NOT EXISTS `kingk_bbcm` (
							`tag` text NOT NULL,
							`phpcallback_class` text,
							`phpcallback_method` text,
							`replacementBegin` longtext,
							`replacementEnd` longtext,
							`description` varchar(255) DEFAULT NULL,
							`title` varchar(255) NOT NULL,
							`requiresOption` int(1) NOT NULL DEFAULT '0',
							`example` varchar(255) NOT NULL,
							`active` int(1) NOT NULL DEFAULT '1',
							`numberOfOptions` int(11) NOT NULL DEFAULT '0',
							`advancedOptions` int(1) NOT NULL DEFAULT '0',
							`regex` varchar(255) DEFAULT NULL,
							`trimLeadingLinesAfter` int(1) NOT NULL DEFAULT '0',
							`plainCallback` int(1) NOT NULL DEFAULT '0',
							`plainChildren` int(1) NOT NULL DEFAULT '0',
							`stopSmilies` int(1) NOT NULL DEFAULT '0',
							`stopLineBreakConversion` int(1) NOT NULL DEFAULT '0'
						  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
			
			$db->query("INSERT INTO `kingk_bbcm` (`tag`, `phpcallback_class`, `phpcallback_method`, `description`, `title`, `requiresOption`, `example`, `active`)
							VALUES ('user', 'KingK_BbCodeManager_BbCode_Formatter_Default', 'parseTagUser', 'Allows linking of to a users profile by providing either the Username (in the tags) or the user ID as an option.', 'User Tag', '0', '[user=\"1\"]The First User![/user]\n[user]Admin[/user]', '1')");
		
			$db->query("INSERT INTO `kingk_bbcm` (`tag`, `phpcallback_class`, `phpcallback_method`, `description`, `title`, `requiresOption`, `example`, `active`)
							VALUES ('flash', 'KingK_BbCodeManager_BbCode_Formatter_Default', 'parseTagFlash', 'The ability to embed flash video content in posts.', 'Flash Tag', '0', '[flash=\"400, 200\"]width,height.swf[/flash]\n[flash]flashMovie.swf[/flash]', '0')");
						
			$db->query("INSERT INTO `kingk_bbcm` (`tag`, `phpcallback_class`, `phpcallback_method`, `description`, `title`, `requiresOption`, `example`, `active`)
							VALUES ('spoiler', 'KingK_BbCodeManager_BbCode_Formatter_Default', 'parseTagSpoiler', 'Adds a spoiler tag to hid and show a div.', 'Spoiler', '0', '[spoiler=\"SPOILS!\"]I just spoiled your mind.[/spoiler]', '1')");
	
			$db->query("INSERT INTO `kingk_bbcm` (`tag`, `replacementBegin`, `replacementEnd`, `description`, `title`, `requiresOption`, `example`, `active`)
							VALUES ('float', '<div style=\"float: %s;\">', '</div><div style=\"clear:both\"></div>', 'Allows the use of a floating div. Option is either left, right, center.', 'Float', '1', '[float=\"right\"]Floating Right![/float]', '1')");
						
			$db->query("INSERT INTO `kingk_bbcm` (`tag`, `replacementBegin`, `replacementEnd`, `description`, `title`, `requiresOption`, `example`, `active`)
							VALUES ('h2', '<h2>', '</h2>', 'Adds a H2 tag around the contained text.', 'H2', '0', '[h2]Header Tag[/h2]', '1')");			
		}

		if(!empty($addon) && $addon['version_id'] < 2)
		{
			$db->query("CREATE TABLE IF NOT EXISTS `kingk_bbcm` (
						  `tag` text NOT NULL,
						  `phpcallback_class` text,
						  `phpcallback_method` text,
						  `replacementBegin` longtext,
						  `replacementEnd` longtext,
						  `description` varchar(255) DEFAULT NULL,
						  `title` varchar(255) NOT NULL,
						  `requiresOption` int(1) NOT NULL DEFAULT '0',
						  `example` varchar(255) NOT NULL,
						  `active` int(1) NOT NULL DEFAULT '1'
						  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
			
			$db->query("INSERT INTO `kingk_bbcm` (`tag`, `phpcallback_class`, `phpcallback_method`, `description`, `title`, `requiresOption`, `example`, `active`)
							VALUES ('user', 'KingK_BbCodeManager_BbCode_Formatter_Default', 'parseTagUser', 'Allows linking of to a users profile by providing either the Username (in the tags) or the user ID as an option.', 'User Tag', '0', '[user=\"1\"]The First User![/user]\n[user]Admin[/user]', '1')");
		
			$db->query("INSERT INTO `kingk_bbcm` (`tag`, `phpcallback_class`, `phpcallback_method`, `description`, `title`, `requiresOption`, `example`, `active`)
							VALUES ('flash', 'KingK_BbCodeManager_BbCode_Formatter_Default', 'parseTagFlash', 'The ability to embed flash video content in posts.', 'Flash Tag', '0', '[flash=\"400,200\"]width,height.swf[/user]\n[flash]flashMovie.swf[/flash]', '0')");
						
			$db->query("INSERT INTO `kingk_bbcm` (`tag`, `phpcallback_class`, `phpcallback_method`, `description`, `title`, `requiresOption`, `example`, `active`)
							VALUES ('spoiler', 'KingK_BbCodeManager_BbCode_Formatter_Default', 'parseTagSpoiler', 'Adds a spoiler tag to hid and show a div.', 'Spoiler', '0', '[spoiler=\"SPOILS!\"]I just spoiled your mind.[/spoiler]', '1')");
	
			$db->query("INSERT INTO `kingk_bbcm` (`tag`, `replacementBegin`, `replacementEnd`, `description`, `title`, `requiresOption`, `example`, `active`)
							VALUES ('float', '<div style=\"float: %s;\">', '</div><div style=\"clear:both\"></div>', 'Allows the use of a floating div. Option is either left, right, center.', 'Float', '1', '[float=\"right\"]Floating Right![/float]', '1')");
						
			$db->query("INSERT INTO `kingk_bbcm` (`tag`, `replacementBegin`, `replacementEnd`, `description`, `title`, `requiresOption`, `example`, `active`)
							VALUES ('h2', '<h2>', '</h2>', 'Adds a H2 tag around the contained text.', 'H2', '0', '[h2]Header Tag[/h2]', '1')");
		}
	
		if(!empty($addon) && $addon['version_id'] < 3)
		{
			$db->query("UPDATE `kingk_bbcm` SET `example`='[flash=\"400, 200\"]width,height.swf[/flash]\n[flash]flashMovie.swf[/flash]' WHERE `tag`='flash'");
			self::addColumnIfNotExist($db, 'numberOfOptions', "int(11) NOT NULL DEFAULT '0'");
			self::addColumnIfNotExist($db, 'advancedOptions', "int(1) NOT NULL DEFAULT '0'");
			self::addColumnIfNotExist($db, 'regex', 'varchar(255) DEFAULT NULL');
			self::addColumnIfNotExist($db, 'trimLeadingLinesAfter', "int(1) NOT NULL DEFAULT '0'");
			self::addColumnIfNotExist($db, 'plainCallback', "int(1) NOT NULL DEFAULT '0'");
			self::addColumnIfNotExist($db, 'plainChildren', "int(1) NOT NULL DEFAULT '0'");
			self::addColumnIfNotExist($db, 'stopSmilies', "int(1) NOT NULL DEFAULT '0'");
			self::addColumnIfNotExist($db, 'stopLineBreakConversion', "int(1) NOT NULL DEFAULT '0'");
		}

		if(empty($addon) || $addon['version_id'] < 10)
		{
			self::addColumnIfNotExist($db, 'hasButton', "int(1) NOT NULL DEFAULT '0'");
			self::addColumnIfNotExist($db, 'button_has_usr', "int(1) NOT NULL DEFAULT '0'");
			self::addColumnIfNotExist($db, 'button_usr', 'TEXT DEFAULT NULL');
			self::addColumnIfNotExist($db, 'killCmd', "int(1) NOT NULL DEFAULT '0'");
			self::addColumnIfNotExist($db, 'custCmd', 'varchar(50) DEFAULT NULL');			
			self::addColumnIfNotExist($db, 'imgMethod', 'varchar(20) DEFAULT NULL');
			self::addColumnIfNotExist($db, 'buttonDesc', 'TINYTEXT DEFAULT NULL');
			self::addColumnIfNotExist($db, 'tagOptions', 'TINYTEXT DEFAULT NULL');
			self::addColumnIfNotExist($db, 'tagContent', 'TINYTEXT DEFAULT NULL');


			$db->query("CREATE TABLE IF NOT EXISTS `kingk_bbcm_buttons` (             
			        		`config_id` INT(200) NOT NULL AUTO_INCREMENT,
						`config_type` TINYTEXT NOT NULL,
						`config_buttons_order` TEXT NOT NULL,
						`config_buttons_full` MEDIUMTEXT NOT NULL,
						PRIMARY KEY (`config_id`)
					)
		                	ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;"
			);

			$db->query("INSERT INTO `kingk_bbcm_buttons` (`config_id`, `config_type`, `config_buttons_order`, `config_buttons_full`) VALUES (1, 'ltr', '', ''), (2, 'rtl', '', '');");	

		}
		if(empty($addon) || $addon['version_id'] < 13)
		{
			$db->query("ALTER TABLE `kingk_bbcm` CHANGE `tag` `tag` VARCHAR(30) NOT NULL");
			$db->query("ALTER TABLE `kingk_bbcm` CHANGE `example` `example` TEXT NOT NULL");
			$db->query("ALTER TABLE `kingk_bbcm` CHANGE `replacementBegin` `replacementBegin` TINYTEXT NULL DEFAULT NULL");
			$db->query("ALTER TABLE `kingk_bbcm` CHANGE `replacementEnd` `replacementEnd` TINYTEXT NULL DEFAULT NULL");
			$db->query("ALTER TABLE `kingk_bbcm` CHANGE `phpcallback_class` `phpcallback_class` TINYTEXT NULL DEFAULT NULL");
			$db->query("ALTER TABLE `kingk_bbcm` CHANGE `phpcallback_method` `phpcallback_method` TINYTEXT NULL DEFAULT NULL");
		}

		if(empty($addon) || $addon['version_id'] < 14)
		{
			//Sorry, my mistake
			$db->query("ALTER TABLE `kingk_bbcm` CHANGE `replacementBegin` `replacementBegin` TEXT NULL DEFAULT NULL");
			$db->query("ALTER TABLE `kingk_bbcm` CHANGE `replacementEnd` `replacementEnd` TEXT NULL DEFAULT NULL");
		}
		
		//Generate simple cache (users don't need anymore to edit a bbcode and save it (without operating any change) to activate the Simple Cache
		self::SimpleCachedActiveCustomBbCodes($db);
	}
	
	public static function uninstall()
	{
		XenForo_Model::create('XenForo_Model_DataRegistry')->delete('kingk_bbcm_buttons');
		XenForo_Application::setSimpleCacheData('kingk_bbcm_active', false);		
		XenForo_Application::get('db')->query("DROP TABLE `kingk_bbcm`");
		XenForo_Application::get('db')->query("DROP TABLE `kingk_bbcm_buttons`");
	}
	
	public static function addColumnIfNotExist(&$db, $field, $attr)
	{
		if ($db->fetchRow('SHOW COLUMNS FROM kingk_bbcm WHERE Field = ?', $field))
		{
			return;
		}
    
		return $db->query('ALTER TABLE kingk_bbcm ADD ' . $field . ' ' . $attr);
	}
	
	public static function SimpleCachedActiveCustomBbCodes($db)
	{
		$active = $db->query("SELECT `tag` FROM `kingk_bbcm` WHERE `active` = 1 ORDER BY `tag` ASC");

		if(is_array($active))
		{
			$string = '';
			foreach ($active as $bbcode)
			{
				 $string .= $bbcode['tag'].',';
			}
			
			$string =  substr($string, 0, -1);
	
			XenForo_Application::setSimpleCacheData('kingk_bbcm_active', $string);
		}
	}
	
}