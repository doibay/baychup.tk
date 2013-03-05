<?php

class XfAddOns_Sitemap_Logger
{
	
	/**
	 * A wrapper for the XenForo debugger. Necessitated by the fact that we have a flag that checks for debug to be enabled
	 * or not
	 */
	public static function debug($msg)
	{
		$options = XenForo_Application::getOptions();
		if (!$options->xfa_sitemap_log_creation)
		{
			return;
		}
		if (!XenForo_Application::debugMode())
		{
			return;
		}		
		
		$e = new Exception($msg);
		XenForo_Error::logException($e, false);
	}
	
	
}