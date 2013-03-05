<?php

class EWRporta_Block_MediaRecent extends XenForo_Model
{
	public function getModule($options)
	{
		if ((!$addon = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('EWRmedio')) || empty($addon['active']))
		{
			return "killModule";
		}

		$recent = $this->getModelFromCache('EWRmedio_Model_Lists')->getMediaList(1, $options['limit']);

		return $recent;
	}
}