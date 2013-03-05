<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Route_Prefix_XenSocialize implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		return $router->getRouteMatch('GFNCoders_XenSocialize_ControllerPublic_XenSocialize', $routePath, 'GFNXenSocialize');
	}
}