<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Option_TwitterConsumerSecret
{
	public static function fetchOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		if(GFNCoders_Helper_Cache::get('GFNXenSocialize_HasTwitterDependency'))
		{
			$model = XenForo_Model::create('XenForo_Model_Option');
			$preparedOption = $model->prepareOption($model->getOptionById('twitterConsumerSecret'));
		} else
			$preparedOption = array(
					'edit_format' => 'textbox',
					'data_type' => 'string',
					'can_backup' => 1,
					'formatParams' => array(),
					'title' => new XenForo_Phrase('option_GFNXenSocialize_Twitter_AppSecret'),
					'explain' => new XenForo_Phrase('option_GFNXenSocialize_Twitter_AppSecret_explain')
				);
		
		$preparedOption['option_id'] = 'GFNXenSocialize_Twitter_AppSecret';
		$preparedOption['option_value'] = XenForo_Application::get('options')->GFNXenSocialize_Twitter_AppSecret;
				
		return XenForo_ViewAdmin_Helper_Option::renderPreparedOptionHtml($view, $preparedOption, $canEdit, $fieldPrefix);
	}
}