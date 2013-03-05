<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_ViewAdmin_Configure extends XenForo_ViewAdmin_Base
{
	public function renderHtml()
	{
		$this->_params['renderedSettings'] = XenForo_ViewAdmin_Helper_Option::renderPreparedOptionsHtml(
				$this, $this->_params['settings'], $this->_params['canEditOptionDefinition']
		);
	}
}