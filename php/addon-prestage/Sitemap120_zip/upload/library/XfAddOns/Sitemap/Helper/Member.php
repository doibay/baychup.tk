<?php

/**
 * Class used to generate the sitemap contents for members
 */
class XfAddOns_Sitemap_Helper_Member extends XfAddOns_Sitemap_Helper_BasePagination
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
	 * Append the information about the members to the sitemap
	 */
	public function generate($totalMembers)
	{
		$this->initialize();

		$db = XenForo_Application::getDb();
		$sql = "
			SELECT * FROM xf_user user
			WHERE user_id > ? AND
				user_state = 'valid' AND
				is_banned = 0
			ORDER BY user.user_id
			";
		$st = new Zend_Db_Statement_Mysqli($db, $sql);
		$st->execute( array( $this->lastId ) );

		while ($data = $st->fetch())
		{
			$url = XenForo_Link::buildPublicLink('canonical:members', $data);
			$this->addUrl($url, $data['register_date']);

			// We may have to break if we reached the limit of members to include in a single file
			$this->lastId = $data['user_id'];
			$totalMembers--;
			if ($totalMembers <= 0)
			{
				break;
			}
		}

		// if we still have data, that means that we did not finish fetching the information
		$this->isFinished = !$st->fetch();
		$st->closeCursor();
	}


}

