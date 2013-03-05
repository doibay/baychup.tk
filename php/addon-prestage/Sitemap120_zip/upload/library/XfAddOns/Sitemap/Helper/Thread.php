<?php

/**
 * Class used to generate the sitemap contents for threads
 */
class XfAddOns_Sitemap_Helper_Thread extends XfAddOns_Sitemap_Helper_BasePagination
{

	/**
	 * Forum Model used for permissions
	 * @var XenForo_Model_Forum
	 */
	private $forumModel;

	/**
	 * Constructor.
	 * Initializes the map with the root set as urlset
	 */
	public function __construct()
	{
		parent::__construct('urlset');
		$this->forumModel = new XenForo_Model_Forum();
	}

	/**
	 * Append the information about the threads to the sitemap
	 */
	public function generate($totalThreads)
	{
		$this->initialize();

		$db = XenForo_Application::getDb();
		$sql = "
			SELECT * FROM xf_thread thread
			WHERE thread_id > ? AND
				discussion_state = 'visible' AND
				discussion_type <> 'redirect'
			ORDER BY thread.thread_id
			";
		$st = new Zend_Db_Statement_Mysqli($db, $sql);
		$st->execute( array( $this->lastId ) );

		while ($data = $st->fetch())
		{
			$this->lastId = $data['thread_id'];

			if ($this->canView($data))
			{
				$url = XenForo_Link::buildPublicLink('canonical:threads', $data);
				$this->addUrl($url, $data['post_date']);

				// We may have to break if we reached the limit of threads to include in a single file
				$totalThreads--;
				if ($totalThreads <= 0)
				{
					break;
				}
			}
		}

		// if we still have data, that means that we did not finish fetching the information
		$this->isFinished = !$st->fetch();
		$st->closeCursor();
	}

	/**
	 * Check if the default (not registered) user can view the forum. We only expose through the sitemap the
	 * information about the forums that are visible to all the public
	 *
	 * @param array $data		array with information for the forum
	 * @return boolean
	 */
	private function canView($data)
	{
		$nodeId = $data['node_id'];

		$errorPhrase = '';
		$nodePermissions = $this->defaultVisitor->getNodePermissions($nodeId);
		return $this->forumModel->canViewForum($data, $errorPhrase, $nodePermissions, $this->defaultVisitor->toArray());
	}


}

