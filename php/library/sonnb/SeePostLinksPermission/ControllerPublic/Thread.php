<?php

/**
 * Product: sonnb - See post's links permission
 * Version: 1.1.2
 * Date: 28th Jan 2013
 * Author: sonnb
 * Website: www.sonnb.com - www.UnderWorldVN.com
 * License: You might not copy or redistribute this addon.
 */
class sonnb_SeePostLinksPermission_ControllerPublic_Thread extends XFCP_sonnb_SeePostLinksPermission_ControllerPublic_Thread
{

	public function actionReply()
	{
		$parent = parent::actionReply();
		
		if ($parent instanceof XenForo_ControllerResponse_View &&
				!$this->_input->inRequest('more_options'))
		{
			if ($parent->params['post'])
			{
				$filterModel = sonnb_SeePostLinksPermission_Model_Filter::getInstance();
				
				if (isset($parent->params['forum']) && $filterModel->isApplicableForum($parent->params['forum']))
				{
					$filterModel->processMessage($parent->params['post'], true);
					
					$parent->params['defaultMessage'] = $this->_getPostModel()->getQuoteTextForPost($parent->params['post']);
				}
			}
		}
		
		return $parent;
	}
}