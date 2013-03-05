<?php

class PageEditor_XenForo_BbCode_Formatter_Wysiwyg extends XFCP_PageEditor_XenForo_BbCode_Formatter_Wysiwyg
{
	public function getTags()
	{
		if (!empty($GLOBALS['PageEditor_isRequestingEditor']))
		{
			return array();
		}
		
		return parent::getTags();
	}
	
	public function filterString($string, array $rendererStates)
	{
		if (!empty($GLOBALS['PageEditor_isRequestingEditor']))
		{
			return $string;
		}
		
		return parent::filterString($string, $rendererStates);
	}
}