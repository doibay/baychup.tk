<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Option_TwitterConsumerKey
{
	public static function fetchOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		if(GFNCoders_Helper_Cache::get('GFNXenSocialize_HasTwitterDependency'))
		{
			$model = XenForo_Model::create('XenForo_Model_Option');
			$preparedOption = $model->prepareOption($model->getOptionById('twitterConsumerKey'));
		} else
			$preparedOption = array(
					'edit_format' => 'textbox',
					'data_type' => 'string',
					'can_backup' => 1,
					'formatParams' => array(),
					'title' => new XenForo_Phrase('option_GFNXenSocialize_Twitter_AppKey'),
					'explain' => new XenForo_Phrase('option_GFNXenSocialize_Twitter_AppKey_explain')
				);
		
		$preparedOption['option_id'] = 'GFNXenSocialize_Twitter_AppKey';
		$preparedOption['option_value'] = XenForo_Application::get('options')->GFNXenSocialize_Twitter_AppKey;
				
		return XenForo_ViewAdmin_Helper_Option::renderPreparedOptionHtml($view, $preparedOption, $canEdit, $fieldPrefix);
	}
}