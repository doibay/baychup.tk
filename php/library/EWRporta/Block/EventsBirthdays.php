<?php

class EWRporta_Block_EventsBirthdays extends XenForo_Model
{
	public function getModule()
	{
		if ((!$addon = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('EWRatendo')) || empty($addon['active']))
		{
			return "killModule";
		}

		date_default_timezone_set(XenForo_Application::get('options')->guestTimeZone);
		list($month, $day) = explode('.', date('n.j'));
		$birthdays = $this->getModelFromCache('EWRatendo_Model_Birthdays')->getBirthdays($month, $day);

		return $birthdays;
	}
}