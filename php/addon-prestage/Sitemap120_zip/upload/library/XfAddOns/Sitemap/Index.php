<?php

/**
 * Class used to generate the sitemap index. Usually, this is the entry point
 * for generating the site-wide sitemap, as this will split the information as needed
 */
class XfAddOns_Sitemap_Index extends XfAddOns_Sitemap_Helper_Base
{

	/**
	 * An array with all the sitemaps that have been generated, to include them in the index
	 * @var array
	 */
	private $sitemaps = array();

	/**
	 * Directory that will store the sitemaps
	 */
	private $sitemapDir;

	/**
	 * The maximum number of URLs that may be included in a single sitemap file
	 * @var int
	 */
	private $maxUrls = -1;

	/**
	 * Map to the options configuration, and holds the features that are enabled in the sitemap (e.g. threads, forums, members)
	 * @var array
	 */
	private $enabledOptions;

	/**
	 * If true, after the sitemap has been generated, we should notify the services that there is a new sitemap available
	 * @var boolean
	 */
	private $isPing;

	/**
	 * If true, a message will be added to the error log when the process completed
	 * @var boolean
	 */
	private $logSuccess;

	/**
	 * Constructor.
	 * Initializes the map with the root set as sitemapindex
	 */
	public function __construct()
	{
		parent::__construct('sitemapindex');

		$options = XenForo_Application::getOptions();
		$this->maxUrls = $options->xenforo_sitemap_max_urls;
		$this->sitemapDir = $options->xenforo_sitemap_directory;
		$this->enabledOptions = $options->xenforo_sitemap_enable;
		$this->isPing = $options->xenforo_sitemap_ping;
		$this->logSuccess = $options->xfa_sitemap_log_creation;
	}

	/**
	 * Generate the sitemap. This method will add the content for forums and threads
	 */
	public function generate()
	{
		if ($this->enabledOptions['forums'])
		{
			$this->generateForums();
		}
		if ($this->enabledOptions['threads'])
		{
			$this->generateThreads();
		}
		if ($this->enabledOptions['members'])
		{
			$this->generateMembers();
		}
		if ($this->enabledOptions['forumsPagination'])
		{
			$this->generateForumsPagination();
		}
		if ($this->enabledOptions['threadsPagination'])
		{
			$this->generateThreadsPagination();
		}

		// generate the index file
		$this->generateIndex();

		// ping Google with the new sitemap
		if ($this->isPing)
		{
			$this->pingServices();
		}

		// push it to the logs, so we can see that something happened
		if ($this->logSuccess)
		{
			$ex = new Exception('Sitemap has been generated');
			XenForo_Error::logException($ex, false);
		}
	}

	/**
	 * Generate the index of the sitemap
	 *
	 */
	private function generateIndex()
	{
		XfAddOns_Sitemap_Logger::debug('Generating index file...');
		$this->initialize();

		$options = XenForo_Application::getOptions();

		foreach ($this->sitemaps as $loc)
		{
			$sitemapNode = $this->dom->createElement('sitemap');
			$this->root->appendChild($sitemapNode);

			$url = $options->boardUrl . '/' . $loc;
			$this->addNode($sitemapNode, 'loc', $url);
		}
		$this->save($this->sitemapDir . '/sitemap.xml');
	}

	/**
	 * Ping the services with the newly generated sitemap
	 */
	private function pingServices()
	{
		$options = XenForo_Application::getOptions();
		$url = $options->boardUrl . '/' . $this->sitemapDir . '/sitemap.xml.gz';
		XfAddOns_Sitemap_Helper_Ping::pingGoogle($url);
		XfAddOns_Sitemap_Helper_Ping::pingBing($url);
	}

	/**
	 * Generate the forum part of the sitemap
	 */
	private function generateForums()
	{
		XfAddOns_Sitemap_Logger::debug('Generating forums...');
		$forums = new XfAddOns_Sitemap_Helper_Forum();
		$forums->generate();
		if (!$forums->isEmpty)
		{
			$this->sitemaps[] = $forums->save($this->getSitemapName('forums'));
		}
	}

	/**
	 * Generate the forum part of the sitemap
	 */
	private function generateForumsPagination()
	{
		XfAddOns_Sitemap_Logger::debug('Generating forums with pagination...');
		$forums = new XfAddOns_Sitemap_Helper_ForumPagination();
		while (!$forums->isFinished)
		{
			XfAddOns_Sitemap_Logger::debug('-- Starting at ' . $forums->lastId . ' and page ' . $forums->lastPage . ' and generating ' . $this->maxUrls .' urls...');

			$forums->generate($this->maxUrls);
			if (!$forums->isEmpty)
			{
				$this->sitemaps[] = $forums->save($this->getSitemapName('forums.pags'));
			}
		}
	}

	/**
	 * Generate the threads part of the sitemap
	 */
	private function generateThreadsPagination()
	{
		XfAddOns_Sitemap_Logger::debug('Generating threads with pagination...');
		$helper = new XfAddOns_Sitemap_Helper_ThreadPagination();
		while (!$helper->isFinished)
		{
			XfAddOns_Sitemap_Logger::debug('-- Starting at ' . $helper->lastId . ' and page ' . $helper->lastPage . ' and generating ' . $this->maxUrls .' urls...');
			$helper->generate($this->maxUrls);
			if (!$helper->isEmpty)
			{
				$this->sitemaps[] = $helper->save($this->getSitemapName('threads.pags'));
			}
		}
	}

	/**
	 * Generate the threads part of the sitemap
	 */
	private function generateThreads()
	{
		XfAddOns_Sitemap_Logger::debug('Generating threads...');

		$threads = new XfAddOns_Sitemap_Helper_Thread();
		while (!$threads->isFinished)
		{
			XfAddOns_Sitemap_Logger::debug('-- Starting at ' . $threads->lastId . ' and generating ' . $this->maxUrls .' urls...');
			$threads->generate($this->maxUrls);
			if (!$threads->isEmpty)
			{
				$this->sitemaps[] = $threads->save($this->getSitemapName('threads'));
			}
		}
	}

	/**
	 * Generate the members part of the sitemap
	 */
	private function generateMembers()
	{
		XfAddOns_Sitemap_Logger::debug('Generating members...');

		$members = new XfAddOns_Sitemap_Helper_Member();
		while (!$members->isFinished)
		{
			XfAddOns_Sitemap_Logger::debug('-- Starting at ' . $members->lastId . ' and generating ' . $this->maxUrls .' urls...');

			$members->generate($this->maxUrls);
			if (!$members->isEmpty)
			{
				$this->sitemaps[] = $members->save($this->getSitemapName('members'));
			}
		}
	}

	/**
	 * Check if the directory in which the sitemap will be stored is writable and
	 * can store the sitemaps
	 * @return boolean
	 */
	public function isDirectoryWritable()
	{
		return is_writable($this->sitemapDir);
	}

	/**
	 * Returns the directory that we are trying to use to store the sitemaps. This method can be
	 * called for debugging the path in which the sitemap is going to be written
	 * @return string
	 */
	public function getBaseDirectory()
	{
		return getcwd() . '/' . $this->sitemapDir;
	}

	/**
	 * This method will generate a unique sitemap name, add it to the local list of sitemaps that are being generated
	 * and return the name
	 * @return string
	 */
	private function getSitemapName($type)
	{
		// figure out an incremental index depending on the sitemap type
		static $typeDict = array();
		if (!isset($typeDict[$type]))
		{
			$typeDict[$type] = 0;
		}
		$idx = ++$typeDict[$type];

		// generate the name
		$name = $this->sitemapDir . '/sitemap.' . $type . '.' . ($idx) . '.xml';
		return $name;
	}


}