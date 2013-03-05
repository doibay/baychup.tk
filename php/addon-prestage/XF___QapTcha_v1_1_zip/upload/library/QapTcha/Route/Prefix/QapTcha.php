<?php

class QapTcha_Route_Prefix_QapTcha implements XenForo_Route_Interface
{	
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithIntegerParam($routePath, $request, 'id');
		$routeMatch = $router->getRouteMatch('QapTcha_ControllerPublic_Index', $action, 'qaptcha', $routePath);
		return $routeMatch;
	}

	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'id');
	}
}