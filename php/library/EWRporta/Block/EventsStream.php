<?php

class EWRporta_Block_EventsStream extends XenForo_Model
{
	public function getModule($options)
	{
		if ((!$addon = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('EWRatendo')) || empty($addon['active']) || empty($options['events']))
		{
			return "killModule";
		}

		$timeNow = XenForo_Application::$time;

		$event = array(
			'service_width' => $options['width'],
			'service_height' => $options['height'],
		);

		if ($result = $this->_getDb()->fetchRow("
			SELECT EWRatendo_events.*, EWRatendo_services.*, EWRatendo_events.service_value2 AS service_value2
			FROM EWRatendo_events
				INNER JOIN EWRatendo_services ON (EWRatendo_services.service_id = EWRatendo_events.service_id)
			WHERE EWRatendo_events.event_id IN (" . $this->_getDb()->quote($options['events']) . ")
				AND EWRatendo_events.event_state = 'visible'
				AND EWRatendo_events.event_strtime < ?
				AND EWRatendo_events.event_endtime > ?
		", array($timeNow, $timeNow)))
		{
			$event = $this->getModelFromCache('EWRatendo_Model_Parser')->parseReplace($event + $result);
		}

		return $event;
	}
}