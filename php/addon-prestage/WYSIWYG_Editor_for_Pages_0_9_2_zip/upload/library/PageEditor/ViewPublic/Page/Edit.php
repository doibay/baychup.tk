<?php

class PageEditor_ViewPublic_Page_Edit extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$GLOBALS['PageEditor_isRequestingEditor'] = true;
		
		$this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
			$this, 'template', $this->_params['template']['template']
		);
		
		unset($GLOBALS['PageEditor_isRequestingEditor']);
	}
}