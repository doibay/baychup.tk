<?php
class PageEditor_Installer {
	/* Start auto-generated lines of code. Change made will be overwriten... */

	protected static $_tables = array();
	protected static $_patches = array(
		array(
			'table' => 'xf_page',
			'field' => 'pageeditor_attach_count',
			'showColumnsQuery' => 'SHOW COLUMNS FROM `xf_page` LIKE \'pageeditor_attach_count\'',
			'alterTableAddColumnQuery' => 'ALTER TABLE `xf_page` ADD COLUMN `pageeditor_attach_count` INT(10) UNSIGNED DEFAULT \'0\'',
			'alterTableDropColumnQuery' => 'ALTER TABLE `xf_page` DROP COLUMN `pageeditor_attach_count`'
		)
	);

	public static function install() {
		$db = XenForo_Application::get('db');

		foreach (self::$_tables as $table) {
			$db->query($table['createQuery']);
		}
		
		foreach (self::$_patches as $patch) {
			$existed = $db->fetchOne($patch['showColumnsQuery']);
			if (empty($existed)) {
				$db->query($patch['alterTableAddColumnQuery']);
			}
		}
		
		self::installCustomized();
	}
	
	public static function uninstall() {
		$db = XenForo_Application::get('db');
		
		foreach (self::$_patches as $patch) {
			$existed = $db->fetchOne($patch['showColumnsQuery']);
			if (!empty($existed)) {
				$db->query($patch['alterTableDropColumnQuery']);
			}
		}
		
		foreach (self::$_tables as $table) {
			$db->query($table['dropQuery']);
		}
		
		self::uninstallCustomized();
	}

	/* End auto-generated lines of code. Feel free to make changes below */
	
	private static function installCustomized() {
		$db = XenForo_Application::get('db');
		
		$db->query("REPLACE INTO `xf_content_type` (content_type, addon_id, fields) VALUES ('pageeditor_page', 'PageEditor', '')");
		$db->query("REPLACE INTO `xf_content_type_field` (content_type, field_name, field_value) VALUES ('pageeditor_page', 'attachment_handler_class', 'PageEditor_AttachmentHandler_Page')");
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
	}
	
	private static function uninstallCustomized() {
		$db = XenForo_Application::get('db');
		
		$db->query("DELETE FROM `xf_content_type` WHERE addon_id = ?", array('PageEditor'));
		$db->query("DELETE FROM `xf_content_type_field` WHERE content_type = ?", array('pageeditor_page'));
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
	}
	
}