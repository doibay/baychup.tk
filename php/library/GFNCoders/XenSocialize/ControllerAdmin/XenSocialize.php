<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_ControllerAdmin_XenSocialize extends XenForo_ControllerAdmin_Abstract
{
	public function actionIndex()
	{
		$viewParams = array(
				'canEditOptionDefinition' => $this->_getOptionsModel()->canEditOptionAndGroupDefinitions(),
				'settings' => $this->_getOptionsModel()->prepareOptions(array_merge($this->_getOptionsModel()->getOptionsInGroup('GFNXenSocialize_Configuration'), $this->_getOptionsModel()->getOptionsByIds(array(
						'facebookAppId', 'facebookAppSecret'
					)))),
				'hasTwitterDependency' => GFNCoders_Helper_Cache::get('GFNXenSocialize_HasTwitterDependency'),
				'hasTheOldVersion' => $this->_getAddOnModel()->getAddOnById('xenCODE_XenSocialize')
			);
		
		return $this->responseView('GFNCoders_XenSocialize_ViewAdmin_Configure', 'GFNXenSocialize_Configuration', $viewParams);
	}
	
	public function actionFetchFbToken()
	{
		$options = XenForo_Application::get('options');
		
		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::RESOURCE_CREATED, "https://www.facebook.com/dialog/oauth?client_id=" . $options->facebookAppId .
				"&client_secret=" . $options->facebookAppSecret .
				"&scope=manage_pages,status_update,publish_stream&redirect_uri=" . XenForo_Link::buildAdminLink('full:xensocialize/fetch/fb-token/process'));
	}
	
	public function actionFetchFbTokenProcess()
	{
		$accessToken = GFNCoders_XenSocialize_Helper_Facebook::getAccessTokenFromCode($this->_input->filterSingle('code', XenForo_Input::STRING));
		
		if(isset($accessToken['access_token']))
			$this->_getOptionsModel()->updateOptions(array(
					'GFNXenSocialize_Facebook_Token' => $accessToken['access_token']
				));
		else
			return $this->responseError('Unexpected error occured.', 500);
		
		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('xensocialize'));
	}
	
	public function actionFetchTwitterToken()
	{
		try{
		$options = XenForo_Application::get('options');
		
		$config = array(
				'callbackUrl' => XenForo_Link::buildAdminLink('full:xensocialize/fetch/twitter-token/process'),
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
	
	public function actionFetchTwitterTokenProcess()
	{
		if($this->_input->filterSingle('denied', XenForo_Input::STRING) || !$this->_input->filterSingle('oauth_token', XenForo_Input::STRING) || !$this->_input->filterSingle('oauth_verifier', XenForo_Input::STRING))
			return $this->responseError('An unexpected error occured.', 500);
		
		$options = XenForo_Application::get('options');
		
		$config = array(
				'callbackUrl' => XenForo_Link::buildAdminLink('full:xensocialize/fetch/twitter-token/process'),
				'siteUrl' => 'http://api.twitter.com/oauth',
				'consumerKey' => $options->GFNXenSocialize_Twitter_AppKey,
				'consumerSecret' => $options->GFNXenSocialize_Twitter_AppSecret
			);
		
		$consumer = new Zend_Oauth_Consumer($config);
		$token = $consumer->getAccessToken($_GET, XenForo_Application::getSimpleCacheData('GFNXenSocialize_TwitterTokenData'));
		XenForo_Application::setSimpleCacheData('GFNXenSocialize_TwitterTokenData', false);
		
		$this->_getOptionsModel()->updateOptions(array(
				'GFNXenSocialize_Twitter_UserName' => $token->screen_name,
				'GFNXenSocialize_Twitter_OAuthToken' => $token->oauth_token,
				'GFNXenSocialize_Twitter_OAuthSecret' => $token->oauth_token_secret
			));
		
		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('xensocialize'));
	}
	
	public function actionMigrate()
	{
		if(!$this->_getAddOnModel()->getAddOnById('xenCODE_XenSocialize'))
			return $this->responseError('Older version of the product could not be found.', 500);
		
		
		$options = XenForo_Application::get('options');
		
		$this->_getOptionsModel()->updateOptions(array(
				'GFNXenSocialize_IncludeOwner' => $options->xenCODE_XS_ITO,
				'GFNXenSocialize_Facebook_Enable' => $options->xenCODE_XS_EFB,
				'facebookAppId' => $options->xenCODE_XS_FBAI,
				//'facebookAppSecret' > $options->xenCODE_XS_FBAS,
				'GFNXenSocialize_Facebook_PageType' => 'fan_page',
				'GFNXenSocialize_Facebook_PageId' => $options->xenCODE_XS_FBPI,
				'GFNXenSocialize_Twitter_Enable' => $options->xenCODE_XS_ET,
				'GFNXenSocialize_Twitter_AppKey' => $options->xenCODE_XS_TCK,
				'GFNXenSocialize_Twitter_AppSecret' => $options->xenCODE_XS_TCS,
				'GFNXenSocialize_Twitter_PrefaceText' => $options->xenCODE_XS_TSP
			));
		
		$includedUserGroups = (array)$options->xenCODE_XS_IUG; $excludedForums = (array)$options->xenCODE_XS_EF;
		$this->_getForumModel()->excludeForumsFromMigration($excludedForums);
		try{	
			foreach($includedUserGroups as $userGroup)
				$this->_getPermissionModel()->updateGlobalPermissionsForUserCollection(array(
						'GFNXenSocialize' => array(
								'publishToNetwork' => 'allow'
							)
					), $userGroup);
		} catch (Exception $e) { }
		return $this->responseMessage('Everything have been migrated succesfully (except for the Facebook App Secret)! Make sure to fetch the Facebook and Twitter Tokens and remove the old version of XenSocialize.');
	}
	
	/**
	 * @return XenForo_Model_Option
	 */
	protected function _getOptionsModel()
	{
		return $this->getModelFromCache('XenForo_Model_Option');
	}
	/**
	 * @return XenForo_Model_AddOn
	 */
	protected function _getAddOnModel()
	{
		return $this->getModelFromCache('XenForo_Model_AddOn');
	}
	/**
	 * @return XenForo_Model_Permission
	 */
	protected function _getPermissionModel()
	{
		return $this->getModelFromCache('XenForo_Model_Permission');
	}
	/**
	 * @return XenForo_Model_Forum
	 */
	protected function _getForumModel()
	{
		return $this->getModelFromCache('XenForo_Model_Forum');
	}
}