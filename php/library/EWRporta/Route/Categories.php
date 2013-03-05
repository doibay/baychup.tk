<?php

/**
 * Extended class for the "categories" route prefix.
 * Fixes the URL generated for categories at depth = 0, when the "Create Pages for Categories"
 * option is disabled. Prepends the generated hash-tag with the forum-index URL.
 *
 * @author Shadab
 * @package GeekPoint_CustomIndex
 */
class EWRporta_Route_Categories extends XFCP_EWRporta_Route_Categories implements XenForo_Route_BuilderInterface
{
	/**
	 * @see XenForo_Route_BuilderInterface::buildLink()
	 */
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		$link = parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);

		if (!$link instanceof XenForo_Link)
		{
			return $link;
		}

		$customRoute = XenForo_Application::get('options')->EWRporta_route;
		return XenForo_Link::buildBasicLink($customRoute, $action, $extension) . strval($link);
	}
}