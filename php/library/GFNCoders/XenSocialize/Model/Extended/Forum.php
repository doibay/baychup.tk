<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Model_Extended_Forum extends XFCP_GFNCoders_XenSocialize_Model_Extended_Forum
{
	public function prepareForumJoinOptions(array $fetchOptions)
	{
		$return = parent::prepareForumJoinOptions($fetchOptions);
		$return['selectFields'] .= ',
					xensocialize.xensocialize_data, xensocialize.facebook, xensocialize.twitter';
		$return['joinTables'] .= '
					LEFT JOIN gfn_xensocialize_forum AS xensocialize ON
						(xensocialize.node_id = forum.node_id)';
		return $return;
	}
	
	public function getXenSocializeDataById($nodeId)
	{
		$return = $this->_getDb()->fetchRow("SELECT * FROM gfn_xensocialize_forum WHERE node_id = ?", array($nodeId));
		
		if($return && !empty($return))
		{
			$return['xensocialize_data'] = unserialize($return['xensocialize_data']);
			$return['facebook'] = unserialize($return['facebook']);
			$return['twitter'] = unserialize($return['twitter']);
		}
		
		return $return;
	}
	
	public function excludeForumsFromMigration(array $ids)
	{
		$db = $this->_getDb();
		
		Xenforo_Db::beginTransaction($db);
		foreach($ids as $id)
		{
			$xensocializeInfo = unserialize($db->fetchOne("SELECT xensocialize_data FROM gfn_xensocialize_forum WHERE node_id = ?", array($id)));
			if(!isset($xensocializeInfo['enable']))
				continue;
			
			unset($xensocializeInfo['enable']);
			
			$dw = XenForo_DataWriter::create('XenForo_DataWriter_Forum');
			$dw->set('xensocialize_data', $xensocializeInfo);
			$dw->setExistingData($id);
			$dw->save();
		}
		
		XenForo_Db::commit($db);
	}
}