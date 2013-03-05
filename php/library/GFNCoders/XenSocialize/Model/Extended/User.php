<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Model_Extended_User extends XFCP_GFNCoders_XenSocialize_Model_Extended_User
{
	public function prepareUserFetchOptions(array $fetchOptions)
	{
		$return = parent::prepareUserFetchOptions($fetchOptions);
		
		if (!empty($fetchOptions['join']))
		{
			if($fetchOptions['join'] & self::FETCH_USER_OPTION)
			{
				$return['selectFields'] .= ',
							xensocialize_option.post_on_network, xensocialize_option.post_on_network_on_reply';
				$return['joinTables'] .= '
							LEFT JOIN gfn_xensocialize_user_option AS xensocialize_option ON
								(xensocialize_option.user_id = user.user_id)';
			}
		}
		
		$return['selectFields'] .= ',
							xensocialize_network.facebook, xensocialize_network.twitter';
		$return['joinTables'] .= '
							LEFT JOIN gfn_xensocialize_user_network AS xensocialize_network ON
								(xensocialize_network.user_id = user.user_id)';
		
		return $return;
	}
	
	public function getXenSocializeDataById($userId)
	{
		return $this->_getDb()->fetchRow("SELECT xf_user.user_id, gfn_xensocialize_user_network.facebook, gfn_xensocialize_user_network.twitter FROM xf_user LEFT JOIN gfn_xensocialize_user_network ON (gfn_xensocialize_user_network.user_id = xf_user.user_id) WHERE xf_user.user_id = ?", array($userId));
	}
	
	public function updateXenSocializeData($data, $network, $userId = null)
	{
		if($userId === null)
			$userId = XenForo_Visitor::getUserId();
		
		if($data != '')
			$data = serialize($data);
		
		$this->_getDb()->query("INSERT INTO gfn_xensocialize_user_network (user_id, facebook, twitter) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE $network = ?", array($userId, $network == 'facebook' ? $data : '', $network == 'twitter' ? $data : '', $data));
	}
}