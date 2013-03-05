<?php

class KingK_BbCodeManager_Model_CustomButtons extends XenForo_Model
{
	/**
	* Get configs by Id
	*/
	public function getConfigById($id)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM kingk_bbcm_buttons
			WHERE config_id = ?
		', $id);
	}

	/**
	* Get configs by type
	*/	
	public function getConfigByType($type)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM kingk_bbcm_buttons
			WHERE config_type = ?
		', $type);
	}
 
	/**
	* Get all Configs
	*/
	public function getAllConfig()
	{
		return $this->fetchAllKeyed('
			SELECT * 
			FROM kingk_bbcm_buttons
			ORDER BY config_type
		', 'config_id');
	}
	
	
	/**
	* Registry Functions (thanks to Jake Bunce ; http://xenforo.com/community/threads/ideal-way-for-cron-to-loop-through-all-users-over-several-runs.33600/#post-382901)
	*/
	
	public function InsertConfigInRegistry()
	{   
		$options['kingk_bbcm_buttons'] = $this->getAllConfig();
		
		//Put Config type (rtl or ltr) as key of the array (just to have a cleaner display)
		$i = 1;
		foreach ($options['kingk_bbcm_buttons'] as $config)
		{
			$key = $config['config_type'];
			$options['kingk_bbcm_buttons'][$key] = $options['kingk_bbcm_buttons'][$i];
			unset($options['kingk_bbcm_buttons'][$i]);
			$i++;
		}
		
		XenForo_Model::create('XenForo_Model_DataRegistry')->set('kingk_bbcm_buttons', $options);
	}

	public function CleanConfigInRegistry()
	{	  
		XenForo_Model::create('XenForo_Model_DataRegistry')->delete('kingk_bbcm_buttons');
	}

}