<?php

class EWRporta_Block_EventsCalendar extends XenForo_Model
{
	public function getModule()
	{
		if ((!$addon = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('EWRatendo')) || empty($addon['active']))
		{
			return "killModule";
		}

		date_default_timezone_set(XenForo_Application::get('options')->guestTimeZone);
		list($week, $month, $year) = explode('.', date('W.n.Y'));
		$year = $week == 52 && $month == 1 ? $year-1 : $year;

		$block = array('month' => $month, 'year' => $year);
		$block['title'] = new XenForo_Phrase('month_'.$block['month']).' '.$block['year'];
		$block['dates'] = $this->getModelFromCache('EWRatendo_Model_Monthly')->getMonthly($block['month'], $block['year'], false);

		list($mNow, $dNow, $yNow, $wNow) = explode('.', date('n.j.Y.W'));
		$today = array('month' => $mNow, 'day' => $dNow, 'year' => $yNow, 'week' => $wNow);

		return array('portal' => true, 'today' => $today, 'block' => $block);
	}
}