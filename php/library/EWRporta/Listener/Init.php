<?php

class EWRporta_Listener_Init
{
	public static function listen(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		if (!$dependencies instanceof XenForo_Dependencies_Public)
		{
			return;
		}

		if (XenForo_Application::get('options')->EWRporta_index)
		{
			$config = new Zend_Config(array(
				'routePrefix'     => 'portal',
				'controllerClass' => 'EWRporta_ControllerPublic_Portal',
				'majorSection'    => 'portal',
				'minorSection'    => 'layout_id'
			));

			EWRporta_Helper_Index::setDefaultRoute($config, $data);
		}
	}
}