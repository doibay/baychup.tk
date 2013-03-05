<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_ControllerPublic_Extended_Register extends XFCP_GFNCoders_XenSocialize_ControllerPublic_Extended_Register
{
	public function actionFacebook()
	{
		$assocUserId = $this->_input->filterSingle('assoc', XenForo_Input::UINT);
		$redirect = $this->_input->filterSingle('redirect', XenForo_Input::STRING);
	
		$options = XenForo_Application::get('options');
	
		$fbRedirectUri = XenForo_Link::buildPublicLink('canonical:register/facebook', false, array(
				'assoc' => ($assocUserId ? $assocUserId : false)
			));
	
		if ($this->_input->filterSingle('reg', XenForo_Input::UINT))
		{
			$redirect = XenForo_Link::convertUriToAbsoluteUri($this->getDynamicRedirect());
			$baseDomain = preg_replace('#^([a-z]+://[^/]+).*$#i', '$1', XenForo_Link::convertUriToAbsoluteUri($options->boardUrl));
			if (strpos($redirect, $baseDomain) !== 0)
				$redirect = XenForo_Link::buildPublicLink('canonical:index');
	
			XenForo_Application::get('session')->set('fbRedirect', $redirect);
	
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL, GFNCoders_XenSocialize_Helper_Facebook::getFacebookRequestUrl($fbRedirectUri));
		}
	
		$fbToken = $this->_input->filterSingle('t', XenForo_Input::STRING);
	
		if (!$fbToken)
		{
			$error = $this->_input->filterSingle('error', XenForo_Input::STRING);
			if ($error == 'access_denied')
				return $this->responseError(new XenForo_Phrase('access_to_facebook_account_denied'));
	
			$code = $this->_input->filterSingle('code', XenForo_Input::STRING);
			if (!$code)
				return $this->responseError(new XenForo_Phrase('error_occurred_while_connecting_with_facebook'));
	
			$token = GFNCoders_XenSocialize_Helper_Facebook::getAccessTokenFromCode($code, $fbRedirectUri);
			$fbError = GFNCoders_XenSocialize_Helper_Facebook::getFacebookRequestErrorInfo($token, 'access_token');
			if ($fbError)
				return $this->responseError(new XenForo_Phrase('error_occurred_while_connecting_with_facebook'));
	
			$fbToken = $token['access_token'];
		}
	
		$fbUser = GFNCoders_XenSocialize_Helper_Facebook::getUserInfo($fbToken);
		$fbError = GFNCoders_XenSocialize_Helper_Facebook::getFacebookRequestErrorInfo($fbUser, 'id');
		if ($fbError)
			return $this->responseError(new XenForo_Phrase('error_occurred_while_connecting_with_facebook'));
	
		$userModel = $this->_getUserModel();
		$userExternalModel = $this->_getUserExternalModel();
	
		$fbAssoc = $userExternalModel->getExternalAuthAssociation('facebook', $fbUser['id']);
		if ($fbAssoc && $userModel->getUserById($fbAssoc['user_id']))
		{
			$redirect = XenForo_Application::get('session')->get('fbRedirect');
	
			GFNCoders_XenSocialize_Helper_Facebook::setUidCookie($fbUser['id']);
			XenForo_Application::get('session')->changeUserId($fbAssoc['user_id']);
			XenForo_Visitor::setup($fbAssoc['user_id']);
	
			XenForo_Application::get('session')->remove('fbRedirect');
			if (!$redirect)
				$redirect = $this->getDynamicRedirect(false, false);
	
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $redirect);
		}
	
		GFNCoders_XenSocialize_Helper_Facebook::setUidCookie(0);
	
		parent::_assertBoardActive('facebook');
	
		if (empty($fbUser['email']))
			return $this->responseError(new XenForo_Phrase('facebook_returned_unknown_error'));
	
		$existingUser = false;
		$emailMatch = false;
		if (XenForo_Visitor::getUserId())
			$existingUser = XenForo_Visitor::getInstance();
		
		else if ($assocUserId)
			$existingUser = $userModel->getUserById($assocUserId);
	
		if (!$existingUser)
		{
			$existingUser = $userModel->getUserByEmail($fbUser['email']);
			$emailMatch = true;
		}
	
		if ($existingUser)
			return $this->responseView('XenForo_ViewPublic_Register_Facebook', 'register_facebook', array(
					'associateOnly' => true,
	
					'fbToken' => $fbToken,
					'fbUser' => $fbUser,
	
					'existingUser' => $existingUser,
					'emailMatch' => $emailMatch,
					'redirect' => $redirect
			));
	
		if (!XenForo_Application::get('options')->get('registrationSetup', 'enabled'))
			$this->_assertRegistrationActive();
	
		if (!empty($fbUser['birthday']))
		{
			$birthdayParts = explode('/', $fbUser['birthday']);
			if (count($birthdayParts) == 3)
			{
				list($month, $day, $year) = $birthdayParts;
				$userAge = $this->_getUserProfileModel()->calculateAge($year, $month, $day);
				if ($userAge < intval($options->get('registrationSetup', 'minimumAge')))
					return $this->responseError(new XenForo_Phrase('sorry_you_too_young_to_create_an_account'));
			}
		}
	
		// give a unique username suggestion
		if (!isset($fbUser['name']))
			$fbUser['name'] = '';
		
		if ($fbUser['name'])
		{
			$i = 2;
			$origName = $fbUser['name'];
			while ($userModel->getUserByName($fbUser['name']))
				$fbUser['name'] = $origName . ' ' . $i++;
		}
	
		return $this->responseView('XenForo_ViewPublic_Register_Facebook', 'register_facebook', array(
				'fbToken' => $fbToken,
				'fbUser' => $fbUser,
				'redirect' => $redirect,
	
				'customFields' => $this->_getFieldModel()->prepareUserFields(
						$this->_getFieldModel()->getUserFields(array('registration' => true)),
						true
				),
	
				'timeZones' => XenForo_Helper_TimeZone::getTimeZones(),
				'tosUrl' => XenForo_Dependencies_Public::getTosUrl()
		), $this->_getRegistrationContainerParams());
	}
	
	public function actionFacebookRegister()
	{
		$return = parent::actionFacebookRegister();
		
		if(!($return instanceof XenForo_ControllerResponse_Error))
		{
			$fbToken = $this->_input->filterSingle('fb_token', XenForo_Input::STRING);
			$fbUser = GFNCoders_XenSocialize_Helper_Facebook::getUserInfo($fbToken);
			
			$data = array(
					'facebook_id' => $fbUser['id'],
					'access_token' => $fbToken
				);
			
			$this->_getUserModel()->updateXenSocializeData($data, 'facebook');
		}
		
		return $return;
	}
	
	protected function _getProviderRegisterResponse(Social_Provider_Abstract $helper)
	{
		XenForo_Application::setSimpleCacheData('GFNXenSocialize_TwitterTokenDataForUser', XenForo_Application::getSession()->get('twitter_token'));
		
		$return = parent::_getProviderRegisterResponse($helper);
		
		$token = XenForo_Application::getSimpleCacheData('GFNXenSocialize_TwitterTokenDataForUser'); XenForo_Application::setSimpleCacheData('GFNXenSocialize_TwitterTokenDataForUser', false);
		
		if(!($return instanceof XenForo_ControllerResponse_Error) && $token != false)
		{
			$data = array(
					'access_token' => $token->oauth_token,
					'oauth_secret' => $token->oauth_token_secret,
					'user_name' => $token->screen_name
				);
			
			$this->_getUserModel()->updateXenSocializeData($data, 'twitter');
		}
		
		return $return;
	}
}