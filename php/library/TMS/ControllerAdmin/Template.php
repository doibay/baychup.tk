<?php

class TMS_ControllerAdmin_Template extends XFCP_TMS_ControllerAdmin_Template
{

	public function actionEdit()
	{
		/* @var $response XenForo_ControllerResponse_View */
		$response = parent::actionEdit();
		if ($response instanceof XenForo_ControllerResponse_View && !empty($response->params['template']))
		{
			$template = &$response->params['template'];
			$inputStyleId = $response->params['style']['style_id'];

			/* @var $modHelper TMS_ControllerHelper_Modification*/
			$modHelper = $this->getHelper('TMS_ControllerHelper_Modification');
			$response->params = array_merge($response->params, $modHelper->getModifications($inputStyleId, array('template_title' => $template['title'])));

			$template += $this->_getTemplateModel()->getTemplateFinalByTitle($template['title'], $inputStyleId);
			$template['template_final'] = $this->_getStylePropertyModel()->replacePropertiesInTemplateForEditor(
				$template['template_final'], $inputStyleId
			);
		}

		return $response;
	}


	public function actionDiff()
	{
		$input = $this->_input->filter(array(
			'template_id' => XenForo_Input::UINT,
			'style_id' => XenForo_Input::UINT,
			'title' => XenForo_Input::STRING,
			'preview' => XenForo_Input::UINT,
		));

		$templateModel = $this->_getTemplateModel();

		if (!$this->_input->inRequest('title')) {
			$template = $templateModel->getTemplateById($input['template_id']);
			$input['title'] = !empty($template['title']) ? $template['title'] : '';
		}

		TMS_Model_Template::$fetchFinalTemplate = 1;
		$template = $templateModel->getEffectiveTemplateByTitle($input['title'], $input['style_id']);

		$style = $this->_getStyleModel()->getStyleById($input['style_id'], true);

		if (!$style) {
			return $this->responseError(new XenForo_Phrase('requested_style_not_found'), 404);
		}
		elseif (!$template)
		{
			return $this->responseError(new XenForo_Phrase('requested_template_not_found'), 404);
		}

		if(is_null($template['template_final']))
		{
			$compiler = new TMS_Template_Compiler($template['template']);
			$modified = $compiler->modifyAndParse($input['title'], $input['style_id']);
			$template['template_final'] = $modified['template_final'];
			$input['preview']= 1;
		}

		$template['template'] = $this->_getStylePropertyModel()->replacePropertiesInTemplateForEditor($template['template'], $input['style_id']);
		$template['template_final'] = $this->_getStylePropertyModel()->replacePropertiesInTemplateForEditor($template['template_final'], $input['style_id']);

		$diff = new Diff_Compare(explode("\n", $template['template']), explode("\n", $template['template_final']));
		$renderer = new Diff_Renderer_Html_SideBySide;
		$diffHtml = $diff->Render($renderer);
		$diffHtml = str_replace('Old Version</th>', new XenForo_Phrase('tms_original_template').'</th>', $diffHtml);
		$diffHtml = str_replace('New Version</th>', new XenForo_Phrase('tms_final_template').'</th>', $diffHtml);

		$viewParams = array(
			'preview' => $input['preview'],
			'diff' => $diffHtml,
			'template' => $template,
			'style' => $style,
		);

		$containerParams = array('containerTemplate' => 'PAGE_CONTAINER_SIMPLE');

		return $this->responseView('TMS_ViewAdmin_TemplateModification_Diff', 'tms_template_diff', $viewParams, $containerParams);
	}

	public function actionSearch()
	{
		/* @var $response XenForo_ControllerResponse_View */
		$response = parent::actionSearch();

		if (!$response instanceof XenForo_ControllerResponse_View || !$this->_input->filterSingle('search', XenForo_Input::UINT)) {
			return $response;
		}

		$defaultStyleId = (XenForo_Application::debugMode()
			? 0
			: XenForo_Application::get('options')->defaultStyleId
		);

		if ($this->_input->inRequest('style_id'))
		{
			$styleId = $this->_input->filterSingle('style_id', XenForo_Input::UINT);
		}
		else
		{
			$styleId = XenForo_Helper_Cookie::getCookie('edit_style_id');
			if ($styleId === false)
			{
				$styleId = $defaultStyleId;
			}
		}

		$input = $this->_input->filter(array(
			'title' => XenForo_Input::STRING,
			'template' => XenForo_Input::STRING,
			'template_state' => array(XenForo_Input::STRING, 'array' => true)
		));

		$conditions = array();
		if (!empty($input['title']))
		{
			$conditions['title'] = $input['title'];
		}
		if (!empty($input['template']))
		{
			// translate @x searches to "{xen:property x" as that is what is stored
			$text = preg_replace('/@property\s*(")?([a-z0-9_]*)/i', '{xen:property \\2', $input['template']);
			$text = preg_replace('/@([a-z0-9_]+)/i', '{xen:property \\1', $text);

			$conditions['template'] = $text;
		}
		if ($styleId && !empty($input['template_state']) && count($input['template_state']) < 3)
		{
			$conditions['modification_state'] = $input['template_state'];
		}

		/* @var $modHelper TMS_ControllerHelper_Modification*/
		$modHelper = $this->getHelper('TMS_ControllerHelper_Modification');
		$response->params = array_merge($response->params, $modHelper->getModifications($styleId, $conditions));

		return $response;
	}

	public function actionText()
	{
		$templateModel = $this->_getTemplateModel();
		TMS_Model_Template::$fetchFinalTemplate = true;

		$styleId = $this->_input->filterSingle('style_id', XenForo_Input::UINT);
		$title = $this->_input->filterSingle('title', XenForo_Input::STRING);
		$template = $templateModel->getEffectiveTemplateByTitle($title, $styleId);

		if(!$template)
		{
			$template['template'] = '';
			$template['template_final'] = '';
		}
		else
		{
			$template['template'] = $this->_getStylePropertyModel()->replacePropertiesInTemplateForEditor($template['template'], $styleId);
			$template['template_final'] = $this->_getStylePropertyModel()->replacePropertiesInTemplateForEditor($template['template_final'], $styleId);
		}


		return $this->responseView('TMS_ViewAdmin_Template_Text', '', array(
			'template' => $template
		));
	}

	public function actionSearchTitle()
	{
		$q = $this->_input->filterSingle('q', XenForo_Input::STRING);
		$styleId = $this->_input->filterSingle('style_id', XenForo_Input::UINT);

		if ($q !== '')
		{
			$users = $this->_getTemplateModel()->getEffectiveTemplateListForStyle(
				$styleId,
				array('title' => array($q , 'r')),
				array('limit' => 10)
			);
		}
		else
		{
			$users = array();
		}

		$viewParams = array(
			'templates' => $users
		);

		return $this->responseView(
			'TMS_ViewAdmin_Template_SearchTitle',
			'',
			$viewParams
		);
	}

	/**
	 * Gets the modification model.
	 *
	 * @return TMS_Model_Modification
	 */
	protected function _getModificationModel()
	{
		return $this->getModelFromCache('TMS_Model_Modification');
	}

	/**
	 * Get the add-on model.
	 *
	 * @return XenForo_Model_AddOn
	 */
	protected function _getAddOnModel()
	{
		return $this->getModelFromCache('XenForo_Model_AddOn');
	}

	/**
	 * Lazy load the style model object.
	 *
	 * @return  XenForo_Model_Style
	 */
	protected function _getStyleModel()
	{
		return $this->getModelFromCache('XenForo_Model_Style');
	}

	/**
	 * Lazy load the template model object.
	 *
	 * @return  XenForo_Model_Template
	 */
	protected function _getTemplateModel()
	{
		return $this->getModelFromCache('XenForo_Model_Template');
	}
}