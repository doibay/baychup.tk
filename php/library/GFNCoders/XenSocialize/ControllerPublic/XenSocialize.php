<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_ControllerPublic_XenSocialize extends XenForo_ControllerPublic_Abstract
{
	public function actionFacebookReLink()
	{
		if(GFNCoders_Helper_Cache::get('GFNXenSocialize_FacebookData'))
			return $this->responseNoPermission();
		
		$redirect = GFNCoders_XenSocialize_Helper_Facebook::getFacebookRequestUrl(XenForo_Link::buildPublicLink('full:xensocialize/facebook/re-link/process'));
		
		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL, $redirect);
	}
	
	public function actionFacebookReLinkProcess()
	{
		if(GFNCoders_Helper_Cache::get('GFNXenSocialize_FacebookData'))
			return $this->responseNoPermission();
		
		$code = $this->_input->filterSingle('code', XenForo_Input::STRING);
		
		if(!$code)
			return $this->responseError(new XenForo_Phrase('error_occurred_while_connecting_with_facebook'));
		
		$token = GFNCoders_XenSocialize_Helper_Facebook::getAccessTokenFromCode($code, XenForo_Link::buildPublicLink('full:xensocialize/facebook/re-link/process'));
		
		$fbError = XenForo_Helper_Facebook::getFacebookRequestErrorInfo($token, 'access_token');
		if ($fbError)
			return $this->responseError(new XenForo_Phrase('error_occurred_while_connecting_with_facebook'));
		
		$fbUser = XenForo_Helper_Facebook::getUserInfo($token['access_token']);
		
		$data = array(
				'facebook_id' => $fbUser['id'],
				'access_token' => $token['access_token']
		);
			
		$this->_getUserModel()->updateXenSocializeData($data, 'facebook');
		
		return $this->responseMessage(new XenForo_Phrase('GFNXenSocialize_facebook_relink_success'));
	}
	
	public function actionTwitterReLink()
	{
		if(GFNCoders_Helper_Cache::get('GFNXenSocialize_TwitterData'))
			return $this->responseNoPermission();
		
		try{
			$options = XenForo_Application::get('options');
		
			$config = array(
					'callbackUrl' => XenForo_Link::buildPublicLink('full:xensocialize/twitter/re-link/process'),
					'siteUrl' => 'http://api.twitter.com/oauth',
					'consumerKey' => $options->GFNXenSocialize_Twitter_AppKey,
					'consumerSecret' => $options->GFNXenSocialize_Twitter_AppSecret
			);
		
			$consumer = new Zend_Oauth_Consumer($config);
			$token = $consumer->getRequestToken();
			XenForo_Application::setSimpleCacheData('GFNXenSocialize_TwitterTokenData', $token);
		
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::RESOURCE_CREATED, $consumer->redirect());
		} catch(Exception $e) { XenForo_Error::logException($e); return $this->responseError('An unexpected error occured.', 500); }
	}
	
	public function actionTwitterReLinkProcess()
	{
		if(GFNCoders_Helper_Cache::get('GFNXenSocialize_TwitterData'))
			return $this->responseNoPermission();
		
		try{
			if($this->_input->filterSingle('denied', XenForo_Input::STRING) || !$this->_input->filterSingle('oauth_token', XenForo_Input::STRING) || !$this->_input->filterSingle('oauth_verifier', XenForo_Input::STRING))
				return $this->responseError('An unexpected error occured.', 500);
			
			$options = XenForo_Application::get('options');
			
			$config = array(
					'callbackUrl' => XenForo_Link::buildPublicLink('full:xensocialize/twitter/re-link/process'),
					'siteUrl' => 'http://api.twitter.com/oauth',
					'consumerKey' => $options->GFNXenSocialize_Twitter_AppKey,
					'consumerSecret' => $options->GFNXenSocialize_Twitter_AppSecret
			);
			
			$consumer = new Zend_Oauth_Consumer($config);
			$token = $consumer->getAccessToken($_GET, XenForo_Application::getSimpleCacheData('GFNXenSocialize_TwitterTokenData'));
			XenForo_Application::setSimpleCacheData('GFNXenSocialize_TwitterTokenData', false);
			
			$data = array(
					'access_token' => $token->oauth_token,
					'oauth_secret' => $token->oauth_token_secret,
					'user_name' => $token->screen_name
			);
				
			$this->_getUserModel()->updateXenSocializeData($data, 'twitter');
			
			return $this->responseMessage(new XenForo_Phrase('GFNXenSocialize_twitter_relink_success'));
		} catch(Exception $e) { XenForo_Error::logException($e); return $this->responseError('An unexpected error occured.', 500); }
	}
	
	protected function _getUserModel()
	{
		return $this->getModelFromCache('XenForo_Model_User');
	}
}