<?php

/**
 * Class used to generate the sitemap contents for forums
 */
class XfAddOns_Sitemap_Helper_AdditionalUrls extends XfAddOns_Sitemap_Helper_Base
{

	/**
	 * Constructor.
	 * Initializes the map with the root set as urlset
	 */
	public function __construct()
	{
		parent::__construct('urlset');
	}

	/**
	 * Append the information about the forums to the sitemap
	 */
	public function generate()
	{
		$this->initialize();
		$options = XenForo_Application::getOptions();

		$urls = preg_split('/[\r\n]+/', $options->xfa_sitemap_urls);
		foreach ($urls as $url)
		{
			$url = trim($url);
			$this->addUrl($url, XenForo_Application::$time);
		}
	}

}