<?php

class PageEditor_XenForo_ViewPublic_Page_View extends XFCP_PageEditor_XenForo_ViewPublic_Page_View
{
	public function renderHtml()
	{
		if (isset($this->_params['templateTitle']) AND isset($this->_params['page']))
		{
			PageEditor_Renderer::preparePageTemplate($this->_params['templateTitle'], $this->_params['page'], $this);
		}
		
		return parent::renderHtml();
	}
}