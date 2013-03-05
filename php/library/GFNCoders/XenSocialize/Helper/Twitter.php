<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Helper_Twitter
{
	public static function postStatus($message, array $options = null)
	{
		$xfOptions = XenForo_Application::get('options');
		
		if($options === null)
		{
			
		}
		
		try{
			if(!$xfOptions->GFNXenSocialize_Twitter_Fallback)
			{
				$token = new Zend_Oauth_Token_Access();
				$token->setToken($options['access_token'])
					  ->setTokenSecret($options['oauth_secret']);
				
				$twitter = new Zend_Service_Twitter(array(
						'username' => $options['user_name'],
						'accessToken' => $token
					));
				
				if(!$twitter->account->verifyCredentials())
					return false;
				
				$twitter->status->update($message);
				
				return true;
			} else
			{
				if(!class_exists('TwitterOAuth'))
					XenForo_Application::autoload('GFNCoders_XenSocialize_Helper_Fallback_TwitterOAuth');
				
				$twitter = new TwitterOAuth($xfOptions->GFNXenSocialize_Twitter_AppKey, $xfOptions->GFNXenSocialize_Twitter_AppSecret, $options['access_token'], $options['oauth_secret']);
				
				$twitter->post('statuses/update', array('status' => $message));
			}
		} catch(Exception $e) { XenForo_Error::logException($e); return false; }
	}
	
	public static function postUpdateOnSitesProfile(array $statusUpdate, array $data = array())
	{
		if(!isset($data['enable']) || empty($data['enable']))
			return false;
		
		$xfOptions = XenForo_Application::get('options');
		
		$options = array(
				'pre_face' => !empty($data['pre_face']) ? $data['pre_face'] : $xfOptions->GFNXenSocialize_Twitter_PrefaceText,
				'post_face' => !empty($data['post_face']) ? $data['post_face'] : $xfOptions->GFNXenSocialize_Twitter_PostfaceText,
				'user_name' => $xfOptions->GFNXenSocialize_Twitter_UserName,
				'access_token' => $xfOptions->GFNXenSocialize_Twitter_OAuthToken,
				'oauth_secret' => $xfOptions->GFNXenSocialize_Twitter_OAuthSecret
			);
		
		$message = !empty($options['pre_face']) ? $options['pre_face'] . ' ' : '';
		
		if($xfOptions->GFNXenSocialize_IncludeOwner && $xfOptions->GFNXenSocialize_IncludeForum)
			$message .= new XenForo_Phrase('GFNXenSocialize_post_site_update_with_everything', $statusUpdate);
		elseif(!$xfOptions->GFNXenSocialize_IncludeOwner && $xfOptions->GFNXenSocialize_IncludeForum)
			$message .= new XenForo_Phrase('GFNXenSocialize_post_site_update_with_forum', $statusUpdate);
		elseif($xfOptions->GFNXenSocialize_IncludeOwner && !$xfOptions->GFNXenSocialize_IncludeForum)
			$message .= new XenForo_Phrase('GFNXenSocialize_post_site_update_with_user', $statusUpdate);
		elseif(!$xfOptions->GFNXenSocialize_IncludeOwner && !$xfOptions->GFNXenSocialize_IncludeForum)
			$message .= new XenForo_Phrase('GFNXenSocialize_post_site_update_with_nothing', $statusUpdate);
		
		$message .= ' ' . GFNCoders_XenSocialize_Helper_UrlShortener::getShortenedUrlForShare($statusUpdate['url']) . ' ';
		
		$message .= !empty($options['post_face']) ? ' ' . $options['post_face'] : '';
		
		return self::postStatus($message, $options);
	}
}