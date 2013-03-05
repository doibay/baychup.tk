<?php

class Dark_AzuCloud_EventListener_TemplateHook
{
	public static function listen($hookName, &$content, array $hookParams, XenForo_Template_Abstract $template)
	{
		if ($hookName == 'ad_below_content')
		{
			$params = $template->getParams();
			$content .= $template->create('dark_azucloud', $params);
		}
	}
}