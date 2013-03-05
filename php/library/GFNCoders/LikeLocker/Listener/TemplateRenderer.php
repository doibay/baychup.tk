<?php //Nulled by VxF.cc
class GFNCoders_LikeLocker_Listener_TemplateRenderer
{
	public static function postRender($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template)
	{
		if($template instanceof XenForo_Template_Public)
		{
			$template->addRequiredExternal('js', 'js/gfncoders/like-locker/jquery.like-locker.min.js');
			$template->addRequiredExternal('css', 'GFNLikeLocker_BBCode');
		}
	}
}