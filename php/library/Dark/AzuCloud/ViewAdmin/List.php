<?php

class Dark_AzuCloud_ViewAdmin_List extends XenForo_ViewAdmin_Base
{
	public function renderJson()
	{
		if (!empty($this->_params['filterView']))
		{
			$this->_templateName = 'dark_azucloud_list_items';
		}

		return null;
	}
}