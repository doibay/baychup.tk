<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Model_XenSocialize extends XenForo_Model
{
	public function buildForumInfo()
	{
		$db = $this->_getDb();
		$forumIds = $db->fetchCol('SELECT node_id FROM xf_forum');
		
		XenForo_Db::beginTransaction($db);
		foreach($forumIds as $id)
			$db->query('INSERT IGNORE INTO `gfn_xensocialize_forum` (`node_id`, `xensocialize_data`, `facebook`, `twitter`) VALUES (?, 0x613a323a7b733a363a22656e61626c65223b733a313a2231223b733a32303a22706f73745f6f6e5f757365725f70726f66696c65223b733a313a2231223b7d, 0x613a333a7b733a363a22656e61626c65223b733a313a2231223b733a383a227072655f66616365223b733a303a22223b733a393a22706f73745f66616365223b733a303a22223b7d, 0x613a333a7b733a363a22656e61626c65223b733a313a2231223b733a383a227072655f66616365223b733a303a22223b733a393a22706f73745f66616365223b733a303a22223b7d)', array($id));
		XenForo_Db::commit($db);
		
		return $this;
	}
	
	public function buildUserOptions()
	{
		$db = $this->_getDb();
		$userIds = $db->fetchCol("SELECT user_id FROM xf_user");
		
		XenForo_Db::beginTransaction($db);
		foreach($userIds as $id)
			$db->query('INSERT IGNORE INTO `gfn_xensocialize_user_option` (`user_id`, `post_on_network`, `post_on_network_on_reply`) VALUES (?, 1, 1)', array($id));
		XenForo_Db::commit($db);
		
		return $this;
	}
}