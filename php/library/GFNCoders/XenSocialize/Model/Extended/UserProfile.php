<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Model_Extended_UserProfile extends XFCP_GFNCoders_XenSocialize_Model_Extended_UserProfile
{
	public function updateStatus($status, $date = null, array $viewingUser = null)
	{
		$return = parent::updateStatus($status, $date, $viewingUser);
		if($return)
		{			
			$input = new XenForo_Input($_POST);
			$option = $input->filterSingle('xensocialize', XenForo_Input::ARRAY_SIMPLE);
			
			if(!empty($option) && GFNCoders_Helper_Cache::get('GFNXenSocialize_PostStatusOnSocialProfile'))
			{
				if(GFNCoders_Helper_Cache::get('GFNXenSocialize_FacebookData') && !empty($option['facebook']))
					GFNCoders_XenSocialize_Helper_Facebook::postStatus(array(
						'message' => $status,
						'link' => XenForo_Link::buildPublicLink('full:members', XenForo_Visitor::getInstance()) . '#profile-post-' . $return
					), GFNCoders_Helper_Cache::get('GFNXenSocialize_FacebookData'));
				
				if(GFNCoders_Helper_Cache::get('GFNXenSocialize_TwitterData') && !empty($option['twitter']))
				GFNCoders_XenSocialize_Helper_Twitter::postStatus($status . ' ' . 
						GFNCoders_XenSocialize_Helper_UrlShortener::getShortenedUrlForShare(XenForo_Link::buildPublicLink('full:members', XenForo_Visitor::getInstance()) . '#profile-post-' . $return),
						GFNCoders_Helper_Cache::get('GFNXenSocialize_TwitterData'));
			}
		}
		return $return;
	}
}