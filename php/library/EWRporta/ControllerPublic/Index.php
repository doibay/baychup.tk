<?php

class EWRporta_ControllerPublic_Index extends XFCP_EWRporta_ControllerPublic_Index
{
	public $perms;

	public function actionIndex()
	{
		$response = parent::actionIndex();
		$options = XenForo_Application::get('options');

		if ($response instanceof XenForo_ControllerResponse_View && $options->EWRporta_globalize['index'])
		{
			$response->params['layout1'] = 'index';
			$response->params['layout2'] = 'portal';
		}

		return $response;
	}

	protected function _preDispatch($action)
	{
		parent::_preDispatch($action);

		$this->perms = $this->getModelFromCache('EWRporta_Model_Perms')->getPermissions();
	}
}