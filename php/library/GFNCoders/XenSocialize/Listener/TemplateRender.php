<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Listener_TemplateRender
{
	public static function renderHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		$params = array_merge($template->getParams(), $hookParams, array(
				'hasTwitterDependency' => GFNCoders_Helper_Cache::get('GFNXenSocialize_HasTwitterDependency')
			));
		
		switch($hookName)
		{
			case 'admin_forum_edit_tabs':
				$contents .= '<li><a>' . new XenForo_Phrase('GFNXenSocialize_xensocialize') . '</a></li>';
				break;
			case 'admin_forum_edit_panes':
				$contents .= $template->create('GFNXenSocialize_Forum_Edit', $params)->render();
				break;
			case 'thread_create':
				$xenSocializeData = unserialize($params['forum']['xensocialize_data']);
				if(GFNCoders_Helper_Cache::get('GFNXenSocialize_PostOnSiteNetwork') && GFNCoders_Helper_Cache::get('GFNXenSocialize_CanPreventAutoPublish') && !empty($xenSocializeData['enable']))
				{
					$search = '<!-- slot: after_options -->';
					$contents = str_replace($search, "$search\n\t\t" . $template->create('GFNXenSocialize_Thread_Create_AutoPublishOption', $params)->render(), $contents);
				}
				break;
		}
		
		if(GFNCoders_Helper_Cache::get('GFNXenSocialize_PostStatusOnSocialProfile'))
		{
			if(!GFNCoders_Helper_Cache::get('XenForo_Model_Forum_Cache'))
				GFNCoders_Helper_Cache::set('XenForo_Model_Forum_Cache', XenForo_Model::create('XenForo_Model_Forum'));
				
			switch($hookName)
			{
				case 'account_preferences_options':
					$search = '<li><label><input type="checkbox" name="default_watch_state"';
					$contents = str_replace($search, $template->create('GFNXenSocialize_Account_Preferences_Options', $params)->render() . "\n\t\t\t\t$search", $contents);
					break;
				case 'account_personal_details_status':
					$contents .= $template->create('GFNXenSocialize_Account_PersonalDetails_Status', $params)->render();
					break;
					
				case 'thread_create':
					$xensocialize = GFNCoders_Helper_Cache::get('XenForo_Model_Forum_Cache')->getXenSocializeDataById($params['forum']['node_id']);
					if(!empty($xensocialize['xensocialize_data']['post_on_user_profile']))
					{
						$search = '<!-- slot: after_options -->';
						$contents = str_replace($search, "$search\n\t\t" . $template->create('GFNXenSocialize_Thread_Create_Options', $params)->render(), $contents);
					}
					break;
				case 'thread_reply':
					$xensocialize = GFNCoders_Helper_Cache::get('XenForo_Model_Forum_Cache')->getXenSocializeDataById($params['forum']['node_id']);
					if(!empty($xensocialize['xensocialize_data']['post_on_user_profile']))
						$contents .= $template->create('GFNXenSocialize_Thread_Reply_Options', $params)->render();
					break;
			}
		}
	}
	
	public static function postRender($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template)
	{
		$params = array_merge($template->getParams(), array(
				'hasTwitterDependency' => GFNCoders_Helper_Cache::get('GFNXenSocialize_HasTwitterDependency')
			));
		
		if(GFNCoders_Helper_Cache::get('GFNXenSocialize_PostStatusOnSocialProfile'))
			switch($templateName)
			{
				case 'thread_create':
				case 'thread_reply':
					$search = '</form>';
					$content = str_replace($search, $search . $template->create('GFNXenSocialize_Copyright', $params)->render(), $content);
					break;
				case 'thread_view':
					if(!GFNCoders_Helper_Cache::get('XenForo_Model_Forum_Cache'))
						GFNCoders_Helper_Cache::set('XenForo_Model_Forum_Cache', XenForo_Model::create('XenForo_Model_Forum'));
					
					$xensocialize = GFNCoders_Helper_Cache::get('XenForo_Model_Forum_Cache')->getXenSocializeDataById($params['forum']['node_id']);
					if(!empty($xensocialize['xensocialize_data']['post_on_user_profile']))
					{
						$search = '<div class="submitUnit">';
						$content = str_replace($search, $search . $template->create('GFNXenSocialize_Quick_Reply_Options', $params)->render(), $content);
					}
					break;
				case 'PAGE_CONTAINER':
					$replace = $template->create('GFNXenSocialize_VisitorTab_StatusUpdate_Options', $params)->render();
					$search = '<span id="visMenuSEdCount"';
					$content = str_replace($search, $replace . $search, $content);
					$search = '<span id="statusUpdateCount"';
					$content = str_replace($search, $replace . $search, $content);
					break;
				case 'member_view':
					$search = '<span id="statusEditorCounter"';
					$replace = $template->create('GFNXenSocialize_MemberView_StatusUpdate_Options', $params)->render();
					$content = str_replace($search, $replace . $search, $content);
					break;
			}
	}
}