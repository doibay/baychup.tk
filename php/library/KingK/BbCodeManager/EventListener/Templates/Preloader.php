<?php

class KingK_BbCodeManager_EventListener_Templates_Preloader
{

	public static function preloader($templateName, array &$params, XenForo_Template_Abstract $template)
	{
		if ($templateName == 'help_bb_codes')
		{
			$template->preloadTemplate('help_custom_bbcodes');
		}
	}

}