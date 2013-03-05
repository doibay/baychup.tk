<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_DataWriter_Extended_Forum extends XFCP_GFNCoders_XenSocialize_DataWriter_Extended_Forum
{
	protected function _getFields()
	{
		return parent::_getFields() + array(
				'gfn_xensocialize_forum' => array(
						'node_id' => array('type' => self::TYPE_UINT, 'default' => array('xf_node', 'node_id'), 'required' => true),
						'xensocialize_data' => array('type' => self::TYPE_SERIALIZED),
						'facebook' => array('type' => self::TYPE_SERIALIZED),
						'twitter' => array('type' => self::TYPE_SERIALIZED)
					)
			);
	}
	
	public function set($field, $value, $tableName = '', array $options = null)
	{
		if(strpos($field, 'xensocialize_data[') !== false)
			return true;
		if(strpos($field, 'facebook[') !== false)
			return true;
		if(strpos($field, 'twitter[') !== false)
			return true;
		
		return parent::set($field, $value, $tableName, $options);
	}
	
	protected function _preSave()
	{
		if(GFNCoders_Helper_Cache::get('GFNXenSocialize_ControllerCache') !== false && (GFNCoders_Helper_Cache::get('GFNXenSocialize_ControllerCache') instanceof XenForo_ControllerAdmin_Forum))
		{
			$input = new XenForo_Input($_REQUEST);
			$dwData = $input->filter(array(
					'xensocialize_data' => XenForo_Input::ARRAY_SIMPLE,
					'facebook' => XenForo_Input::ARRAY_SIMPLE,
					'twitter' => XenForo_Input::ARRAY_SIMPLE
				));
			$this->bulkSet($dwData);
		}
		
		return parent::_preSave();
	}
}