<?php //Nulled by VxF.cc
class GFNCoders_LikeLocker_Option_LockerStyle
{
	public static function fetchOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$preparedOption['edit_format'] = 'select';
		$preparedOption['formatParams'] = array(
				'default' => 'Default',
				'facebook-style' => 'Facebook Style',
				'facebook-style-plane' => 'Facebook Style (Plain)',
				'classic' => 'Cassic',
				'breeze' => 'Breeze',
				'clarity' => 'Clarity',
				'airiness' => 'Airiness'
			);
		
		return XenForo_ViewAdmin_Helper_Option::renderPreparedOptionHtml($view, $preparedOption, $canEdit, $fieldPrefix);
	}
}