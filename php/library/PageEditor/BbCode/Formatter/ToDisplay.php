<?php

class PageEditor_BbCode_Formatter_ToDisplay extends XenForo_BbCode_Formatter_Base
{
	public function getTags()
	{
		$tags = parent::getTags();
		
		foreach (array_keys($tags) as $key) {
			if ($key != 'attach') {
				unset($tags[$key]);
			}
		}
		
		return $tags;
	}
	
	public function filterString($string, array $rendererStates)
	{
		return $string;
	}
}