<?php
class GFNCoders_UrlRedirector_Route_Prefix_Redirect implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		return $router->getRouteMatch('GFNCoders_UrlRedirector_ControllerPublic_Redirect', 'index', 'gfn_redirect');
	}
}