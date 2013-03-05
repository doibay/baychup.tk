<?php

class KingK_BbCodeManager_Route_PrefixAdmin_CustomBbCodes implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithStringParam($routePath, $request, 'tag');

		return $router->getRouteMatch('KingK_BbCodeManager_ControllerAdmin_CustomBbCodes', $action);
	}

	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, 'tag');
	}
}