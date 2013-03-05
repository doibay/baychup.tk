<?php

class EWRporta_Block_Wikinav extends XenForo_Model
{
	public function getModule($options)
	{
		if ((!$addon = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('EWRcarta')) || empty($addon['active']))
		{
			return "killModule";
		}

		$wikinav['index'] = $this->getModelFromCache('EWRcarta_Model_Lists')->getIndex();

		if ($options['links'])
		{
			$wikinav['pages'] = $this->getModelFromCache('EWRcarta_Model_Lists')->getSideList();
		}
		else
		{
			$wikinav['pages'] = array();
		}

		return $wikinav;
	}
}