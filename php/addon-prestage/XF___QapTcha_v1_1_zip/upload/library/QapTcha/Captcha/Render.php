<?php

class QapTcha_Captcha_Render
{
	public static function captcha_render(array &$extraChoices, XenForo_View $view, array $preparedOption)
	{
		$extraChoices['QapTcha'] = new XenForo_Phrase('qaptcha_selected');
	}
}