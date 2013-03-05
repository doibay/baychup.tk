<?php

class EWRporta_Block_Twitter extends XenForo_Model
{
	public function getModule(&$options)
	{
		$options['features']['scroll'] = $options['features']['scroll'] ? 'true' : 'false';
		$options['features']['hashtags'] = $options['features']['hashtags'] ? 'true' : 'false';
		$options['features']['timestamps'] = $options['features']['timestamps'] ? 'true' : 'false';
		$options['features']['avatars'] = $options['features']['avatars'] ? 'true' : 'false';
		$options['features']['toptweets'] = $options['features']['toptweets'] ? 'true' : 'false';

		return;
	}
}