<?php

class EWRporta_Block_RanksToplist extends XenForo_Model
{
	public function getModule($options)
	{
		if ((!$addon = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('EWRtorneo')) || empty($addon['active']))
		{
			return "killModule";
		}

		$league = $this->getModelFromCache('EWRtorneo_Model_Leagues')->getLeagueById($options['league']);
		$league['ranks'] = $this->getModelFromCache('EWRtorneo_Model_Ranks')->getRanksList(1, $options['limit'], 'rank', 'ASC', 'league', $options['league']);

		return $league;
	}
}