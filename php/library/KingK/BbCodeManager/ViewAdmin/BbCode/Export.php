<?php

class KingK_BbCodeManager_ViewAdmin_BbCode_Export extends XenForo_ViewAdmin_Base
{
	public function renderXml()
	{
		XenForo_Application::autoload('Zend_Debug');
		$this->setDownloadFileName('bbcode_' . $this->_params['bbcode']['tag'] . '.xml');
		return $this->_params['xml']->saveXml();
	}
}