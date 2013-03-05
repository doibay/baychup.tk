<?php

/**
 * Product: sonnb - See post's links permission
 * Version: 1.1.2
 * Date: 28th Jan 2013
 * Author: sonnb
 * Website: www.sonnb.com - www.UnderWorldVN.com
 * License: You might not copy or redistribute this addon.
 */
class sonnb_SeePostLinksPermission_ControllerPublic_Post extends XFCP_sonnb_SeePostLinksPermission_ControllerPublic_Post
{

	public function actionLike()
	{
		$parent = parent::actionLike();
		
		/*
		 * Redirect if user do a like/unlike on filtered post
		 *
		 */
		if ($this->_request->isPost() &&
				$parent instanceof XenForo_ControllerResponse_View)
		{
			$filterModel = sonnb_SeePostLinksPermission_Model_Filter::getInstance();
			
			if (isset($parent->params['forum']) && 
					$filterModel->isApplicableForum($parent->params['forum']) && 
					$parent->params['post']['user_id'] != $filterModel->visitor['user_id'] &&
					(($filterModel->postCondition == 'first' && $parent->params['post']['position'] == 0) ||
					$filterModel->postCondition == 'all') && 
					$parent->params['post']['canLike'] && 
					$filterModel->isContainLinks($parent->params['post']) &&
					$filterModel->checkCondition == 'post_like')
			{
				return $this->getPostSpecificRedirect($parent->params['post'], $parent->params['thread']);
			}
		}
		
		return $parent;
	}
	
	public function actionQuote()
	{
		$parent = parent::actionQuote();
		
		if ($parent instanceof XenForo_ControllerResponse_View)
		{
			if ($parent->params['post'])
			{
				$filterModel = sonnb_SeePostLinksPermission_Model_Filter::getInstance();
			
				if (isset($parent->params['forum']) && $filterModel->isApplicableForum($parent->params['forum']))
				{
					$filterModel->processMessage($parent->params['post'], true);
						
					$parent->params['quote'] = $this->_getPostModel()->getQuoteTextForPost($parent->params['post']);
				}
			}	
		}
		
		return $parent;
	}
}