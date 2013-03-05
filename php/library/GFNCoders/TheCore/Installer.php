<?php //Nulled by VxF.cc
/**
 * 
 * @package GoodForNothing Core
 * @author nCODE (GoodForNothing Coders) <https://www.gfncoders.com/>
 *
 */
abstract class GFNCoders_TheCore_Installer
{
	private static $_queries = array();
	protected static $_isFirstTime = false;
	
	protected static $_mysqlDataLocation;
	
	public static function install($existingAddOn, $addOnData)
	{
		if(!self::$_mysqlDataLocation)
			return false;
		
		$startVersionId = 1;
		$endVersionId = $addOnData['version_id'];
		
		if($existingAddOn)
			$startVersionId = $existingAddOn['version_id'] + 1;
		else 
			self::$_isFirstTime = true;
		
		for($i = $startVersionId; $i <= $endVersionId; $i++)
			self::fetchAndParseContents($i);
		
			self::execute();
	}
	
	public static function uninstall($addOnData)
	{
		if(!self::$_mysqlDataLocation)
			return false;
		
		$startVersionId = $addOnData['version_id'];
		$endVersionId = 1;
		
		for ($i = $startVersionId; $i >= $endVersionId; $i--)
			self::fetchAndParseContents($i);
		
		self::execute();
	}
	
	private static function fetchAndParseContents($versionId)
	{
		$_cache = '';
		try {
			if(file_exists(self::$_mysqlDataLocation . "version_$versionId.sql"))
			{
				$lines = file(self::$_mysqlDataLocation . "version_$versionId.sql");
				
				foreach($lines as $line)
				{
					$line = trim($line);
					if(substr($line, 0, 2) != "--")
						if($line != '')
							$_cache .= " ".$line;
				}
				
				$queries = explode(";", $_cache);
				foreach($queries as $query)
					if(preg_match('/[a-zA-Z0-9]/i', $query))
						self::$_queries[] = $query;
			}
		} catch(Exception $e) { XenForo_Error::logException($e); }
	}
	
	private static function execute()
	{
		try {
			$db = XenForo_Application::getDb();
			XenForo_Db::beginTransaction($db);
				foreach(self::$_queries as $query)
					$db->query($query);
			XenForo_Db::commit($db);
		} catch(Exception $e) { XenForo_Error::logException($e); }
	}
}