<?php

/**
 * Base class for generating SiteMap information. Holds common logic like generating the root node
 * and common functions shared between the generators
 */
class XfAddOns_Sitemap_Helper_BasePagination extends XfAddOns_Sitemap_Helper_Base
{

	/**
	 * The identifier for the last element that was generated. We can use this to resume
	 * the generation of the Sitemap
	 * @var int
	 */
	public $lastId = -1;

	/**
	 * If we are doing pagination (threads in a forum, posts in a thread), we can use this to
	 * resume in the appropiate page. Initialized in 2, since we asume that page 1 is always rendered
	 * and we need to render from page 2 onwards
	 *
	 * @var int
	 */
	public $lastPage = 2;

	/**
	 * true, if the generation of threads finished, false, if there are still more threads that
	 * need to be generated
	 * @var int
	 */
	public $isFinished = false;

}