<?php //Nulled by VxF.cc
class GFNCoders_Helper_Cache
{
	public static $_caches;
	
	private function __construct() { }
	
	public static function set($index, $value)
	{
		$index = strtolower($index);
		if(isset(self::$_caches[$index]) && $value === false)
			unset(self::$_caches[$index]);
		else
			self::$_caches[$index] = $value;
	}
	
	public static function get($index)
	{
		$index = strtolower($index);
		if(isset(self::$_caches[$index]))
			return self::$_caches[$index];
		
		return false;
	}
}