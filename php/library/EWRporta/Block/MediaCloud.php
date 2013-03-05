<?php

class EWRporta_Block_MediaCloud extends XenForo_Model
{
	public function getModule($options)
	{
		if ((!$addon = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('EWRmedio')) || empty($addon['active']))
		{
			return "killModule";
		}

		$cloud['keywords'] = $this->getModelFromCache('EWRmedio_Model_Keywords')->getKeywordCloud($options['limit'], $options['mincloud'], $options['maxcloud']);

		if ($options['animated'] && $cloud['keywords'])
		{
			$cloud['animated'] = $this->getModelFromCache('EWRmedio_Model_Keywords')->getAnimatedCloud($cloud['keywords']);
		}

		return $cloud;
	}
}