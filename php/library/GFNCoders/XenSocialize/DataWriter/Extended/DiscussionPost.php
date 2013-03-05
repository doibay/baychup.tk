<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_DataWriter_Extended_DiscussionPost extends XFCP_GFNCoders_XenSocialize_DataWriter_Extended_DiscussionPost
{
	protected function _messagePostSave()
	{
		parent::_messagePostSave();
		
		if(!$this->isInsert() || $this->isDiscussionFirstMessage())
			return;
		
		$postData = $this->getMergedData();
		$threadData = $this->getModelFromCache('XenForo_Model_Thread')->getThreadById($postData['thread_id']);
		
		$options = XenForo_Application::get('options');
		$visitor = XenForo_Visitor::getInstance();
		
		$input = new XenForo_Input($_POST);
		
		$page = floor(($threadData['reply_count'] + 1) / $options->messagesPerPage) + 1;
		$url = XenForo_Link::buildPublicLink('full:threads', $threadData, array('page' => $page)) . '#post-' . $postData['post_id'];
				
		$statusUpdate = array(
				'url' => $url,
				'title' => $threadData['title'],
				'user' => $visitor['username'],
				'board' => $options->boardTitle
		);
		
		if(GFNCoders_Helper_Cache::get('GFNXenSocialize_PostStatusOnSocialProfile'))
		{
			$postUserOptions = $input->filterSingle('xensocialize', XenForo_Input::ARRAY_SIMPLE);
			
			if(GFNCoders_Helper_Cache::get('GFNXenSocialize_FacebookData') && !empty($postUserOptions['facebook']))
				GFNCoders_XenSocialize_Helper_Facebook::postStatus(array(
						'message' => (string)new XenForo_Phrase('GFNXenSocialize_post_user_profile_post', $statusUpdate),
						'link' => $url
				), GFNCoders_Helper_Cache::get('GFNXenSocialize_FacebookData'));
			
			if(GFNCoders_Helper_Cache::get('GFNXenSocialize_TwitterData') && !empty($postUserOptions['twitter']))
				GFNCoders_XenSocialize_Helper_Twitter::postStatus((string)new XenForo_Phrase('GFNXenSocialize_post_user_profile_post', $statusUpdate) . 
						' ' . GFNCoders_XenSocialize_Helper_UrlShortener::getShortenedUrlForShare($url),
						GFNCoders_Helper_Cache::get('GFNXenSocialize_TwitterData'));
		}
	}
}