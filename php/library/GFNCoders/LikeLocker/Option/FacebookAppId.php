<?php //Nulled by VxF.cc
class GFNCoders_LikeLocker_Option_FacebookAppId
{
	public static function fetchOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$model = XenForo_Model::create('XenForo_Model_Option');
		$preparedOption = $model->prepareOption($model->getOptionById('facebookAppId'));
		return XenForo_ViewAdmin_Helper_Option::renderPreparedOptionHtml($view, $preparedOption, $canEdit, $fieldPrefix);
	}
}