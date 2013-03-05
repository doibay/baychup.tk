<?php

class KingK_BbCodeManager_EventListener_BbCode
{
    public static function listen($class, array &$extend)
    {
        if ($class == 'XenForo_BbCode_Formatter_Base')
        {
            $extend[] = 'KingK_BbCodeManager_BbCode_Formatter_Base';
        }
        
      	if ($class == 'XenForo_ControllerPublic_Help')
      	{
      		$extend[] = 'KingK_BbCodeManager_ControllerPublic_Help';
      	}
    }
}