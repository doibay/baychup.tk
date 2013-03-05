<?php

class Dark_AzuCloud_Install
{
	/**
	 * Instance manager.
	 *
	 * @var Dark_TaigaChat_Install
	 */
	private static $_instance;

	/**
	 * Database object
	 *
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_db;

	/**
	 * Gets the installer instance.
	 *
	 * @return Dark_TaigaChat_Install
	 */
	public static final function getInstance()
	{
		if (!self::$_instance)
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Helper method to get the database object.
	 *
	 * @return Zend_Db_Adapter_Abstract
	 */
	protected function _getDb()
	{
		if ($this->_db === null)
		{
			$this->_db = XenForo_Application::get('db');
		}

		return $this->_db;
	}

	/**
	 * Begins the installation process and picks the proper install routine.
	 *
	 * See see XenForo_Model_Addon::installAddOnXml() for more details about
	 * the arguments passed to this method.
	 *
	 * @param array Information about the existing version (if upgrading)
	 * @param array Information about the current version being installed
	 *
	 * @return void
	 */
	public static function install($existingAddOn, $addOnData)
	{
		// the version IDs from which we should start/end the install process
		$startVersionId = 1;
		$endVersionId = $addOnData['version_id'];

		if ($existingAddOn)
		{
			// we are upgrading, run every install method since last upgrade
			$startVersionId = $existingAddOn['version_id'] + 1;
		}

		// create our install object
		$install = self::getInstance();

		for ($i = $startVersionId; $i <= $endVersionId; $i++)
		{
			$method = '_installVersion' . $i;
			if (method_exists($install, $method) === false)
			{
				continue;
			}

			$install->$method();
		}

	}

	/**
	 * Install routine for version ID 1 
	 *
	 * @return void
	 */
	protected function _installVersion1()
	{
		$db = $this->_getDb();

		$db->query("
			CREATE TABLE `dark_azucloud_terms` (
				`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`value` VARCHAR(255) NOT NULL,
				`block` TINYINT(1) UNSIGNED NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `value` (`value`),
				INDEX `block` (`block`)
			)
			COLLATE='utf8_general_ci'
			ENGINE=InnoDB
		");
		
		$db->query("
			CREATE TABLE `dark_azucloud_terms_pages` (
				`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`term_id` INT(10) UNSIGNED NOT NULL,
				`route` VARCHAR(255) NOT NULL,
				`hits` INT(10) UNSIGNED NOT NULL,
				`last_clicked` INT(10) UNSIGNED NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `term_id_route` (`term_id`, `route`),
				INDEX `route` (`route`)
			)
			COLLATE='utf8_general_ci'
			ENGINE=InnoDB
		");
	}
	/**
	 * Install routine for version ID 6 
	 *
	 * @return void
	 */
	protected function _installVersion6()
	{
		$db = $this->_getDb();
		$db->query("
			alter table dark_azucloud_terms_pages disable keys;
		");
		$db->query("
			ALTER TABLE `dark_azucloud_terms_pages`
				CHANGE COLUMN `route` `route` VARCHAR(150) NOT NULL AFTER `term_id`;
		");
		$db->query("
			alter table dark_azucloud_terms_pages enable keys;
		");
		$db->query("
			ALTER TABLE `dark_azucloud_terms_pages`
			ADD INDEX `hits_last_clicked` (`hits`, `last_clicked`);
		");        
		$db->query("
			ALTER TABLE `dark_azucloud_terms_pages`
				ADD INDEX `route_hits_last_clicked` (`route`, `hits`, `last_clicked`);
		");       
	}

}
