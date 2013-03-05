<?php

class EWRporta_Block_EventsUpcoming extends XenForo_Model
{
	public function getModule(&$options)
	{
		if ((!$addon = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('EWRatendo')) || empty($addon['active']))
		{
			return "killModule";
		}

		$events = $this->getModelFromCache('EWRatendo_Model_Events')->getCurrentEvents(
			'+'.$options['range'].' '.$options['format'],
			false,
			$options['stream'],
			false
		);

		$options['showStream'] = $options['stream'] ? true : false;
		$options['hideVenue'] = $options['venue'] ? false : true;

		return $events;
	}
}