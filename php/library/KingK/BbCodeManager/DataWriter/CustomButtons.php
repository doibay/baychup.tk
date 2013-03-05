<?php

class KingK_BbCodeManager_DataWriter_CustomButtons extends XenForo_DataWriter
{
	protected function _getFields() {
		return array(
			'kingk_bbcm_buttons' => array(
				'config_id' 	=> array(
						'type' => self::TYPE_UINT,
				                'autoIncrement' => true
				),
				'config_type' 	=> array(
						'type' => self::TYPE_STRING, 
						'default' => ''
				),
				'config_buttons_order' 	=> array(
						'type' => self::TYPE_STRING, 
						'default' => ''
				),
				'config_buttons_full' => array(
						'type' => self::TYPE_STRING, 
						'default' => ''
				)
			)
		);
	}
	
	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data, 'config_id'))
		{
			return false;
		}
		return array('kingk_bbcm_buttons' => $this->_getCustomButtonsModel()->getConfigById($id));
	}
	
	protected function _getUpdateCondition($tableName)
	{
		return 'config_id = ' . $this->_db->quote($this->getExisting('config_id'));
	}

	protected function _getCustomButtonsModel()
	{
		return $this->getModelFromCache('KingK_BbCodeManager_Model_CustomButtons');
	}	
}