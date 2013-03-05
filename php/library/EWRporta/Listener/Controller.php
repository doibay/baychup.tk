<?php

class EWRporta_Listener_Controller
{
    public static function controller($class, array &$extend)
    {
		switch ($class)
		{
			case 'XenForo_ControllerPublic_Thread':
				$extend[] = 'EWRporta_ControllerPublic_Thread';
				break;
			case 'XenForo_ControllerPublic_Index':
				$extend[] = 'EWRporta_ControllerPublic_Index';
				break;
			case 'XenForo_ControllerPublic_Forum':
				$extend[] = 'EWRporta_ControllerPublic_Forum';
				break;
		}
    }
}