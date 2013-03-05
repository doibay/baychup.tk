<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_DataWriter_Extended_DiscussionThread extends XFCP_GFNCoders_XenSocialize_DataWriter_Extended_DiscussionThread
{
	protected function _discussionPostSave(array $messages)
	{
		parent::_discussionPostSave($messages);
		
		if(!$this->isInsert())
			return;
		
		$threadData = $this->getMergedData();
		
		$options = XenForo_Application::get('options');
		$visitor = XenForo_Visitor::getInstance();
		
		$nodeInfo = $this->getModelFromCache('XenForo_Model_Forum')->getForumById($threadData['node_id']);
		$xenSocializeData = unserialize($nodeInfo['xensocialize_data']);
		
		$input = new XenForo_Input($_POST);
		
		$url = XenForo_Link::buildPublicLink('full:threads', $threadData);
		
		$statusUpdate = array(
				'url' => $url,
				'title' => $threadData['title'],
				'user' => $visitor['username'],
				'forum' => $nodeInfo['title'],
				'board' => $options->boardTitle
			);
		
		if(!empty($xenSocializeData['post_on_user_profile']) && GFNCoders_Helper_Cache::get('GFNXenSocialize_PostStatusOnSocialProfile'))
		{
			$postUserOptions = $input->filterSingle('xensocialize', XenForo_Input::ARRAY_SIMPLE);
			if(GFNCoders_Helper_Cache::get('GFNXenSocialize_FacebookData') !== false && !empty($postUserOptions['facebook']))
				GFNCoders_XenSocialize_Helper_Facebook::postStatus(array(
						'message' => (string)new XenForo_Phrase('GFNXenSocialize_post_user_profile_thread', $statusUpdate),
						'link' => $url
					), GFNCoders_Helper_Cache::get('GFNXenSocialize_FacebookData'));
			
			if(GFNCoders_Helper_Cache::get('GFNXenSocialize_TwitterData') && !empty($postUserOptions['twitter']))
				GFNCoders_XenSocialize_Helper_Twitter::postStatus((string)new XenForo_Phrase('GFNXenSocialize_post_user_profile_thread', $statusUpdate) .
						' ' . GFNCoders_XenSocialize_Helper_UrlShortener::getShortenedUrlForShare($url),
						GFNCoders_Helper_Cache::get('GFNXenSocialize_TwitterData'));
		}
		
		if(!GFNCoders_Helper_Cache::get('GFNXenSocialize_PostOnSiteNetwork'))
			return;
		
		if(empty($xenSocializeData['enable']))
			return;
		
		if($input->filterSingle('xensocialize_do_not_post', XenForo_Input::UINT))
			return;
		
		if(in_array($threadData['node_id'], (array)$options->GFNXenSocialize_DisabledForums))
			return;
		
		if($options->GFNXenSocialize_Facebook_Enable)
			GFNCoders_XenSocialize_Helper_Facebook::postUpdateOnSitesProfile($statusUpdate, unserialize($nodeInfo['facebook']));
		
		if($options->GFNXenSocialize_Twitter_Enable)
			GFNCoders_XenSocialize_Helper_Twitter::postUpdateOnSitesProfile($statusUpdate, unserialize($nodeInfo['twitter']));
	}
}