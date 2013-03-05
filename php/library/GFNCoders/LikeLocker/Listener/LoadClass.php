<?php //Nulled by VxF.cc
class GFNCoders_LikeLocker_Listener_LoadClass
{
	public static function extendClass($class, array &$extend)
	{
		switch($class)
		{
			case 'XenForo_Model_Post':
				$extend[] = 'GFNCoders_LikeLocker_Model_Post';
				break;
				
			case 'XenForo_BbCode_Formatter_Base':
				$extend[] = 'GFNCoders_LikeLocker_BBCode_Formatter_Base';
				break;
		}
	}
}