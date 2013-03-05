<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Listener_PreDispatch
{
	public static function checkDependencies()
	{
		try{
			if(XenForo_Model::create('XenForo_Model_AddOn')->checkIfAddOnExistsAndEnabled('Social'))
				GFNCoders_Helper_Cache::set('GFNXenSocialize_HasTwitterDependency', true);
			
			$visitor = XenForo_Visitor::getInstance();
			
			$data = array(
					'facebook' => isset($visitor['facebook']) ? $visitor['facebook'] : null,
					'twitter' => isset($visitor['twitter']) ? $visitor['twitter'] : null
				);
			
			if(!empty($data['facebook']))
				GFNCoders_Helper_Cache::set('GFNXenSocialize_FacebookData', unserialize($data['facebook']));
			if(!empty($data['twitter']))
				GFNCoders_Helper_Cache::set('GFNXenSocialize_TwitterData', unserialize($data['twitter']));
			
			$permissions = $visitor->getPermissions();
			if(!empty($permissions['GFNXenSocialize']['publishToNetwork']))
				GFNCoders_Helper_Cache::set('GFNXenSocialize_PostOnSiteNetwork', true);
			if(!empty($permissions['GFNXenSocialize']['canPreventAutoPub']))
				GFNCoders_Helper_Cache::set('GFNXenSocialize_CanPreventAutoPublish', true);
			if(!empty($permissions['GFNXenSocialize']['postOnProfile']))
				GFNCoders_Helper_Cache::set('GFNXenSocialize_PostStatusOnSocialProfile', true);
			
		} catch(Exception $e) { XenForo_Error::logException($e); }
	}
}