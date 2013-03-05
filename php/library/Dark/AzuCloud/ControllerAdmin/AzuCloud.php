<?php
	
class Dark_AzuCloud_ControllerAdmin_AzuCloud extends XenForo_ControllerAdmin_Abstract
{
	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('option');
	}
	
	protected function _filterTermSearchCriteria(array $criteria)
	{
		foreach ($criteria AS $key => $value)
		{
			if ($value === '')
			{
				unset($criteria[$key]);
			}
			else
			{
				switch ($key)
				{
					case 'id':
					case 'term_id':
						if ($value === '0' || $value === 0)
						{
							unset($criteria[$key]);
						}
				}
			}
		}

		return $criteria;
	}

	protected function _prepareTermSearchCriteria(array $criteria)
	{
		if (!empty($criteria['last_clicked']))
		{
			$criteria['last_clicked'] = array('>=',
				XenForo_Input::rawFilter($criteria['last_clicked'], XenForo_Input::DATE_TIME)
			);
		}

		if (!empty($criteria['hits']))
		{
			$criteria['hits'] = array('>=', $criteria['hits']);
		}

		return $criteria;
	}
	
	
	public function actionBlock()
	{		
		$azumodel = $this->getModelFromCache('Dark_AzuCloud_Model_Nakano');
		
		$id = $this->_input->filterSingle('term_id', XenForo_Input::UINT);
		
		$dw = XenForo_DataWriter::create('Dark_AzuCloud_DataWriter_Term');
		$dw->setExistingData($id);
		
		$term = $azumodel->getTermById($id);

		if($term['block'])
		{
			$dw->set('block', 0);
		}
		else
		{
			$dw->set('block', 1);
		}
		
		$dw->save();
		
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			$this->getDynamicRedirect(XenForo_Link::buildAdminLink('azucloud/list'))
		);
	}
		
	public function actionList()
	{
		$azumodel = $this->getModelFromCache('Dark_AzuCloud_Model_Nakano');
		
		$criteria = $this->_input->filterSingle('criteria', XenForo_Input::ARRAY_SIMPLE);
		$criteria = $this->_filterTermSearchCriteria($criteria);

		$filter = $this->_input->filterSingle('_filter', XenForo_Input::ARRAY_SIMPLE);
		if ($filter && isset($filter['value']))
		{
			$criteria['value'] = array($filter['value'], empty($filter['prefix']) ? 'lr' : 'r');
			$filterView = true;
		}
		else
		{
			$filterView = false;
		}

		$order = $this->_input->filterSingle('order', XenForo_Input::STRING);
		$direction = $this->_input->filterSingle('direction', XenForo_Input::STRING);
		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$termsPerPage = 50;

		$fetchOptions = array(
			'perPage' => $termsPerPage,
			'page' => $page,

			'order' => $order,
			'direction' => $direction
		);


		$criteriaPrepared = $this->_prepareTermSearchCriteria($criteria);

		$totalTerms = $azumodel->countTerms($criteriaPrepared);
		if (!$totalTerms)
		{
			return $this->responseError(new XenForo_Phrase('no_results_found'));
		}

		$terms = $azumodel->getTerms($criteriaPrepared, $fetchOptions);

		$viewParams = array(
			'terms' => $terms,
			'totalTerms' => $totalTerms,

			'linkParams' => array('criteria' => $criteria, 'order' => $order, 'direction' => $direction),
			'page' => $page,
			'termsPerPage' => $termsPerPage,
			
			'orderParams' => array(
				'hits' => array('order' => 'hits', 'direction' => 'desc'),
				'last_clicked' => array('order' => 'last_clicked', 'direction' => 'desc'),
				'value' => array('order' => 'value', 'direction' => 'asc')
			),

			'filterView' => $filterView,
			'filterMore' => ($filterView && $totalTerms > $termsPerPage)
		);
		
		return $this->responseView('Dark_AzuCloud_ViewAdmin_List', 'dark_azucloud_list', $viewParams);
	}
	
}