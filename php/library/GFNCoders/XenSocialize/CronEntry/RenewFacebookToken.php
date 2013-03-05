<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_CronEntry_RenewFacebookToken
{
	public static function renewToken()
	{
		GFNCoders_XenSocialize_Helper_Facebook::renewAccessToken(XenForo_Application::get('options')->GFNXenSocialize_Facebook_Token);
	}
}