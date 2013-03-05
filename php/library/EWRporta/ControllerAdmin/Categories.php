<?php

class EWRporta_ControllerAdmin_Categories extends XenForo_ControllerAdmin_Abstract
{
	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('node');
	}

	public function actionIndex()
	{
		$viewParams = array(
			'categories' => $this->getModelFromCache('EWRporta_Model_Categories')->getCategories()
		);

		return $this->responseView('EWRporta_ViewAdmin_Categories', 'EWRporta_Categories', $viewParams);
	}

	public function actionAdd()
	{
		$viewParams = array(
			'category' => array()
		);

		return $this->responseView('EWRporta_ViewAdmin_EditCategory', 'EWRporta_EditCategory', $viewParams);
	}

	public function actionEdit()
	{
		$categorySlug = $this->_input->filterSingle('category_slug', XenForo_Input::STRING);

		if (!$category = $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryBySlug($categorySlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/categories'));
		}

		$viewParams = array(
			'category' => $category
		);

		return $this->responseView('EWRporta_ViewAdmin_EditCategory', 'EWRporta_EditCategory', $viewParams);
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		$input = $this->_input->filter(array(
			'category_id' => XenForo_Input::UINT,
			'category_name' => XenForo_Input::STRING,
			'category_slug' => XenForo_Input::STRING,
		));

		$this->getModelFromCache('EWRporta_Model_Categories')->updateCategory($input);

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/categories'));
	}

	public function actionDelete()
	{
		$categorySlug = $this->_input->filterSingle('category_slug', XenForo_Input::STRING);

		if (!$category = $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryBySlug($categorySlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/categories'));
		}

		if ($this->isConfirmedPost())
		{
			$this->getModelFromCache('EWRporta_Model_Categories')->deleteCategory($category);
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/categories'));
		}
		else
		{
			$viewParams = array(
				'category' => $category
			);

			return $this->responseView('EWRporta_ViewAdmin_DeleteCategory', 'EWRporta_DeleteCategory', $viewParams);
		}
	}





	public function actionEdit1()
	{
		$layoutId = $this->_input->filterSingle('layout_id', XenForo_Input::STRING);
		$layoutType = $this->_input->filterSingle('layout_type', XenForo_Input::STRING);

		if (!empty($layoutType))
		{
			$layoutId = $layoutId ? $layoutType.'-'.$layoutId : $layoutType;
		}

		if (empty($layoutId))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/layouts'));
		}

		$isPortal = (substr($layoutId, 0, 8) === 'articles') || $layoutId == 'portal' ? true : false;

		$blocks = $this->getModelFromCache('EWRporta_Model_Blocks')->getBlocks(false, $layoutId, 'portal');

		$_blocks = array(
			'top-left' => array(),
			'top-right' => array(),
			'mid-left' => array(),
			'mid-right' => array(),
			'btm-left' => array(),
			'btm-right' => array(),
			'sidebar' => array(),
			'disabled' => array()
		);

		foreach ($blocks AS $block)
		{
			if ($isPortal)
			{
				switch ($block['position'])
				{
					case 'mid-left':	$_blocks['mid-left'][] = $block;	continue 2;
					case 'mid-right':	$_blocks['mid-right'][] = $block;	continue 2;
				}
			}

			switch ($block['position'])
			{
				case 'top-left':	$_blocks['top-left'][] = $block;	break;
				case 'top-right':	$_blocks['top-right'][] = $block;	break;
				case 'btm-left':	$_blocks['btm-left'][] = $block;	break;
				case 'btm-right':	$_blocks['btm-right'][] = $block;	break;
				case 'sidebar':		$_blocks['sidebar'][] = $block;		break;
				default:			$_blocks['disabled'][] = $block;
			}
		}

		$viewParams = array(
			'layout' => array('layout_id' => $layoutId),
			'blocks' => $_blocks,
			'isPortal' => $isPortal,
			'content' => strtoupper($layoutId),
		);

		return $this->responseView('EWRporta_ViewAdmin_EditLayout', 'EWRporta_EditLayout', $viewParams);
	}

	public function actionSave1()
	{
		$this->_assertPostOnly();

		$input = $this->_input->filter(array(
			'layout_id' => XenForo_Input::STRING,
			'blocks' => XenForo_Input::ARRAY_SIMPLE,
		));

		$this->getModelFromCache('EWRporta_Model_Layouts')->updateLayout($input);

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/layouts/edit', $input));
	}

	public function actionInstallConfirm()
	{
		return $this->responseView('EWRporta_ViewAdmin_InstallLayout', 'EWRporta_InstallLayout');
	}

	public function actionInstall()
	{
		$this->_assertPostOnly();

		$fileTransfer = new Zend_File_Transfer_Adapter_Http();
		if ($fileTransfer->isUploaded('upload_file'))
		{
			$fileInfo = $fileTransfer->getFileInfo('upload_file');
			$fileName = $fileInfo['upload_file']['tmp_name'];
		}
		else
		{
			$fileName = $this->_input->filterSingle('server_file', XenForo_Input::STRING);
		}

		$this->getModelFromCache('EWRporta_Model_Layouts')->installLayoutXmlFromFile($fileName);

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/layouts'));
	}

	public function actionExport()
	{
		$layoutId = $this->_input->filterSingle('layout_id', XenForo_Input::STRING);

		if (!$layout = $this->getModelFromCache('EWRporta_Model_Layouts')->getLayoutById($layoutId))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/layouts'));
		}

		$this->_routeMatch->setResponseType('xml');

		$viewParams = array(
			'layout' => $layout,
			'xml' => $this->getModelFromCache('EWRporta_Model_Layouts')->exportLayout($layout),
		);

		return $this->responseView('EWRporta_ViewAdmin_ExportLayout', '', $viewParams);
	}
}