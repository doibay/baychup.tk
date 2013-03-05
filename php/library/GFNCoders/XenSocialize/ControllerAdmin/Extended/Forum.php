<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_ControllerAdmin_Extended_Forum extends XFCP_GFNCoders_XenSocialize_ControllerAdmin_Extended_Forum
{
	public function actionEdit()
	{
		$return = parent::actionEdit();
		$forumParams = $return->params['forum'];
		$return->params['forum']['xensocialize_data'] = isset($forumParams['xensocialize_data']) ? unserialize($forumParams['xensocialize_data']) : array();
		$return->params['forum']['facebook'] = isset($forumParams['facebook']) ? unserialize($forumParams['facebook']) : array();
		$return->params['forum']['twitter'] = isset($forumParams['twitter']) ? unserialize($forumParams['twitter']) : array();
		
		return $return;
	}
	
	public function actionSave()
	{
		GFNCoders_Helper_Cache::set('GFNXenSocialize_ControllerCache', $this);
		
		return parent::actionSave();
	}
}