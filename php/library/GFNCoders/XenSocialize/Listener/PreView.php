<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Listener_PreView
{
	public static function initiate(XenForo_FrontController $fc, XenForo_ControllerResponse_Abstract &$controllerResponse, XenForo_ViewRenderer_Abstract &$viewRenderer, array &$containerParams)
	{
		$visitor = XenForo_Visitor::getInstance();
		
		if(!empty($visitor['facebook_auth_id']) && empty($visitor['facebook']))
			$containerParams['notices'][9999999] = array(
					'title' => new XenForo_Phrase('GFNXenSocialize_notice_relink_facebook_title'),
					'message' => new XenForo_Phrase('GFNXenSocialize_notice_relink_facebook', array('user' => $visitor['username'], 'href' => XenForo_Link::buildPublicLink('xensocialize/facebook/re-link'))),
					'wrap' => true,
					'dismissible' => false
			);
		if(!empty($visitor['twitter_auth_id']) && empty($visitor['twitter']))
			$containerParams['notices'][10000000] = array(
					'title' => new XenForo_Phrase('GFNXenSocialize_notice_relink_twitter_title'),
					'message' => new XenForo_Phrase('GFNXenSocialize_notice_relink_twitter', array('user' => $visitor['username'], 'href' => XenForo_Link::buildPublicLink('xensocialize/twitter/re-link'))),
					'wrap' => true,
					'dismissible' => false
			);
	}
}