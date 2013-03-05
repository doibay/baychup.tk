<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Helper_Facebook extends XenForo_Helper_Facebook
{
	public static function postStatus(array $statusUpdate, array $options = null)
	{
		$xfOptions = XenForo_Application::get('options');
		
		if($options === null)
		{
			
		}
		
		try{
			if(!isset($options['access_token']))
				return false;
			
			self::renewAccessToken($options['access_token']);
			
			if(!$xfOptions->GFNXenSocialize_Facebook_Fallback)
			{
				$client = XenForo_Helper_Http::getClient('https://graph.facebook.com/' . $options['facebook_id'] . '/feed');
				$client->setParameterPost(array(
						'access_token' => $options['access_token'],
						'message' => $statusUpdate['message'],
						'link' => $statusUpdate['link']
					));
					
				$client->request('POST');
				
				return true;
			} else
			{
				if(!class_exists('Facebook'))
					XenForo_Application::autoload('GFNCoders_XenSocialize_Helper_Fallback_Facebook');
				
				$facebook = new Facebook(array(
						'appId' => $xfOptions->facebookAppId,
						'secret' => $xfOptions->facebookAppSecret
					));
				
				$facebook->api("/{$options['facebook_id']}/feed", 'POST', array(
						'access_token' => $options['access_token'],
						'message' => $statusUpdate['message'],
						'link' => $statusUpdate['link']
					));
			}
		} catch(Exception $e) { XenForo_Error::logException($e); return false; }
	}
	
	public static function postStatusOnPage(array $statusUpdate, array $options)
	{
		if(!isset($options['access_token']))
			return false;
		
		self::renewAccessToken($options['access_token']);
		
		$pageToken = self::_getPageToken($options['access_token'], $options['facebook_id']);
		if(!$pageToken)
			return false;
		
		$xfOptions = XenForo_Application::get('options');
		
		try{
			if(!$xfOptions->GFNXenSocialize_Facebook_Fallback)
			{
				$client = XenForo_Helper_Http::getClient('https://graph.facebook.com/' . $options['facebook_id'] . '/feed');
				$client->setParameterPost(array(
						'access_token' => $pageToken,
						'message' => $statusUpdate['message'],
						'link' => $statusUpdate['link']
					));
				
				$client->request('POST');
				
				return true;
			} else
			{
				if(!class_exists('Facebook'))
					XenForo_Application::autoload('GFNCoders_XenSocialize_Helper_Fallback_Facebook');
				
				$facebook = new Facebook(array(
						'appId' => $xfOptions->facebookAppId,
						'secret' => $xfOptions->facebookAppSecret
				));
				
				$facebook->api("/{$options['facebook_id']}/feed", 'POST', array(
						'access_token' => $pageToken,
						'message' => $statusUpdate['message'],
						'link' => $statusUpdate['link']
				));
			}
		} catch(Exception $e) { XenForo_Error::logException($e); return false; }
	}
	
	protected static function _getPageToken($accessToken, $pageId)
	{
		try{
			$client = XenForo_Helper_Http::getClient('https://graph.facebook.com/' . $pageId . '?fields=access_token');
			$client->setParameterGet('access_token', $accessToken);
				
			$response = $client->request('GET');
			$output = json_decode($response->getBody(), true);
			if(isset($output['access_token']))
				return $output['access_token'];
			else return false;
		} catch(Exception $e) { XenForo_Error::logException($e); return false; }
	}
	
	public static function postUpdateOnSitesProfile(array $statusUpdate, array $data = array())
	{
		if(!isset($data['enable']) || empty($data['enable']))
			return false;
		
		$xfOptions = XenForo_Application::get('options');
		
		$options = array(
				'facebook_id' => isset($data['different_page']) && !empty($data['different_page']['id']) ?  $data['different_page']['id'] : $xfOptions->GFNXenSocialize_Facebook_PageId,
				'page_type' => isset($data['different_page']) ? $data['different_page']['type'] : $xfOptions->GFNXenSocialize_Facebook_PageType,
				'pre_face' => !empty($data['pre_face']) ? $data['pre_face'] : $xfOptions->GFNXenSocialize_Facebook_PrefaceText,
				'post_face' => !empty($data['post_face']) ? $data['post_face'] : $xfOptions->GFNXenSocialize_Facebook_PostfaceText,
				'access_token' => $xfOptions->GFNXenSocialize_Facebook_Token
			);
		
		$update['link'] = $statusUpdate['url'];
		$update['message'] = !empty($options['pre_face']) ? $options['pre_face'] . ' ' : '';
		
		if($xfOptions->GFNXenSocialize_IncludeOwner && $xfOptions->GFNXenSocialize_IncludeForum)
			$update['message'] .= new XenForo_Phrase('GFNXenSocialize_post_site_update_with_everything', $statusUpdate);
		elseif(!$xfOptions->GFNXenSocialize_IncludeOwner && $xfOptions->GFNXenSocialize_IncludeForum)
			$update['message'] .= new XenForo_Phrase('GFNXenSocialize_post_site_update_with_forum', $statusUpdate);
		elseif($xfOptions->GFNXenSocialize_IncludeOwner && !$xfOptions->GFNXenSocialize_IncludeForum)
			$update['message'] .= new XenForo_Phrase('GFNXenSocialize_post_site_update_with_user', $statusUpdate);
		elseif(!$xfOptions->GFNXenSocialize_IncludeOwner && !$xfOptions->GFNXenSocialize_IncludeForum)
			$update['message'] .= new XenForo_Phrase('GFNXenSocialize_post_site_update_with_nothing', $statusUpdate);
		
		$update['message'] .= !empty($options['post_face']) ? ' ' . $options['post_face'] : '';
		
		if($options['page_type'] == 'profile')
			$options['facebook_id'] = 'me';
		
		if($options['page_type'] == 'fan_page')
			return self::postStatusOnPage($update, $options);
		else
			return self::postStatus($update, $options);
	}
	
	public static function renewAccessToken($token)
	{
		try{
			$options = XenForo_Application::get('options');
			
			$client = XenForo_Helper_Http::getClient('https://graph.facebook.com/oauth/access_token');
			$client->setParameterGet(array(
					'client_id' => $options->facebookAppId,
					'client_secret' => $options->facebookAppSecret,
					'grant_type' => 'fb_exchange_token',
					'fb_exchange_token' => $token
				));
			
			if($client->request()->getBody() == "access_token=$token")
				return $token;
			else
				return false;
		} catch(Exception $e) { XenForo_Error::logException($e); return false; }
	}
	
	public static function getFacebookRequestUrl($redirectUri, $appId = null)
	{
		$perms = 'email,publish_stream,user_birthday,user_status,user_website,user_location,status_update,publish_stream';
	
		if (!$appId)
			$appId = XenForo_Application::get('options')->facebookAppId;
	
		return 'https://graph.facebook.com/oauth/authorize?client_id=' . $appId
		. '&scope=' . $perms
		. '&redirect_uri=' . urlencode($redirectUri);
	}
}