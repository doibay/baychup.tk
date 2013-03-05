<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Listener_LoadClass
{
	public static function extendClass($class, array &$extend)
	{
		switch($class)
		{
			// DataWriters...
			case 'XenForo_DataWriter_Forum':
				$extend[] = 'GFNCoders_XenSocialize_DataWriter_Extended_Forum';
				break;
			case 'XenForo_DataWriter_User':
				$extend[] = 'GFNCoders_XenSocialize_DataWriter_Extended_User';
				break;
			case 'XenForo_DataWriter_Discussion_Thread':
				$extend[] = 'GFNCoders_XenSocialize_DataWriter_Extended_DiscussionThread';
				break;
			case 'XenForo_DataWriter_DiscussionMessage_Post':
				$extend[] = 'GFNCoders_XenSocialize_DataWriter_Extended_DiscussionPost';
				break;
			
			// Models...
			case 'XenForo_Model_Forum':
				$extend[] = 'GFNCoders_XenSocialize_Model_Extended_Forum';
				break;
			case 'XenForo_Model_User':
				$extend[] = 'GFNCoders_XenSocialize_Model_Extended_User';
				break;
			case 'XenForo_Model_UserProfile':
				$extend[] = 'GFNCoders_XenSocialize_Model_Extended_UserProfile';
				break;
			case 'XenForo_Model_AddOn':
				$extend[] = 'GFNCoders_XenSocialize_Model_Extended_AddOn';
				break;
			
			// ClassControllers...
			case 'XenForo_ControllerAdmin_Forum':
				$extend[] = 'GFNCoders_XenSocialize_ControllerAdmin_Extended_Forum';
				break;
			case 'XenForo_ControllerPublic_Register':
				$extend[] = 'GFNCoders_XenSocialize_ControllerPublic_Extended_Register';
				break;
			case 'XenForo_ControllerPublic_Account':
				$extend[] = 'GFNCoders_XenSocialize_ControllerPublic_Extended_Account';
				break;
		}
	}
}