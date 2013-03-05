<?php

class Waindigo_NoDowntime_Listener_InitDependencies
{
	public static function initDependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		XenForo_CacheRebuilder_Abstract::$builders['Template'] = 'Waindigo_NoDowntime_CacheRebuilder_Template';
	}
}