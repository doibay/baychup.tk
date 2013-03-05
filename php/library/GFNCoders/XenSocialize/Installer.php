<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Installer extends GFNCoders_TheCore_Installer
{
	public static function install($existingAddOn, $addOnData)
	{
		self::$_mysqlDataLocation = dirname(__FILE__) . "/MySQL/Data/Install/";
		parent::install($existingAddOn, $addOnData);
		
		if(self::$_isFirstTime)
			XenForo_Model::create('GFNCoders_XenSocialize_Model_XenSocialize')->buildForumInfo()->buildUserOptions();
		
		if($existingAddOn && $existingAddOn['version_id'] <= 9)
			XenForo_Model::create('GFNCoders_XenSocialize_Model_XenSocialize')->buildUserOptions();
	}
	
	public static function uninstall($addOnData)
	{
		self::$_mysqlDataLocation = dirname(__FILE__) . "/MySQL/Data/Uninstall/";
		parent::uninstall($addOnData);
	}
}