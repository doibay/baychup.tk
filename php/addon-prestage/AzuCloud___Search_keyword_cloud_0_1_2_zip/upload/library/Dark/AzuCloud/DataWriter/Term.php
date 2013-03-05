<?php

class Dark_AzuCloud_DataWriter_Term extends XenForo_DataWriter
{
	
	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		$options = XenForo_Application::get('options');
		return array(
			'dark_azucloud_terms' => array(
				'id'      	   => array('type' => self::TYPE_UINT,   'autoIncrement' => true),
				'value'      => array('type' => self::TYPE_STRING,   'required' => true),
				'block'         => array('type' => self::TYPE_UINT,   'required' => true)
			)
		);
	}

	/**
	* Gets the actual existing data out of data that was passed in. See parent for explanation.
	*
	* @param mixed
	*
	* @return array|false
	*/
	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data))
		{
			return false;
		}

		return array('dark_azucloud_terms' => $this->_getModel()->getTermById($id));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'id = ' . $this->_db->quote($this->getExisting('id'));
	}


	/**
	 * Adds any number of responses to the poll. Blank options are ignored.
	 *
	 * @param array $responses
	 */
	public function addResponses(array $responses)
	{
		foreach ($responses AS $key => $response)
		{
			if (!is_string($response) || $response === '')
			{
				unset($responses[$key]);
			}
		}
		$this->_newResponses = array_merge($this->_newResponses, $responses);
	}

	/**
	 * Determines if the poll has new responses.
	 *
	 * @return boolean
	 */
	public function hasNewResponses()
	{
		return (count($this->_newResponses) > 0);
	}

	/**
	 * Pre-save handling.
	 */
	protected function _preSave()
	{		
	}

	/**
	 * Post-save handling.
	 */
	protected function _postSave()
	{
		
	}

	/**
	 * Post-delete handling.
	 */
	protected function _postDelete()
	{
		
	}

	/**
	 * @return XenForo_Model_Poll
	 */
	protected function _getModel()
	{
		return $this->getModelFromCache('Dark_AzuCloud_Model_Nakano');
	}
}