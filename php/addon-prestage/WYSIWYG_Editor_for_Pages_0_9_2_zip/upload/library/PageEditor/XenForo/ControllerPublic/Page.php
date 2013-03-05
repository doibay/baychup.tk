<?php

class PageEditor_XenForo_ControllerPublic_Page extends XFCP_PageEditor_XenForo_ControllerPublic_Page
{
	public function actionIndex()
	{
		if ($this->_input->filterSingle('edit', XenForo_Input::UINT) > 0)
		{
			return $this->responseReroute('XenForo_ControllerPublic_Page', 'edit');
		}
		
		return parent::actionIndex();
	}
	
	public function actionEdit()
	{
		$nodeName = $this->_input->filterSingle('node_name', XenForo_Input::STRING);
		$page = $this->_getPageOrError($nodeName);
		
		$attachmentModel = $this->getModelFromCache('XenForo_Model_Attachment');
		$pageModel = $this->_getPageModel();
		$templateModel = $this->getModelFromCache('XenForo_Model_Template');
		
		if (!$pageModel->PageEditor_canEditPage($page))
		{
			return $this->responseNoPermission();
		}

		if ($this->_request->isPost())
		{
			$input = $this->_input->filter(array(
				'attachment_hash' => XenForo_Input::STRING,
				'template_id' => XenForo_Input::UINT,
			));
			$input['template'] = $this->getHelper('Editor')->getMessageText('template', $this->_input);
			$input['template'] = XenForo_Helper_String::autoLinkBbCode($input['template']);
			
			$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('PageEditor_BbCode_Formatter_ToSave', array()));
			$input['template'] = $bbCodeParser->render($input['template']);
			
			$GLOBALS['PageEditor_XenForo_ControllerPublic_Page#actionEdit'] = $this;
			$pageModel->savePage(array(), $input['template'], $page['node_id'], $input['template_id']);
			unset($GLOBALS['PageEditor_XenForo_ControllerPublic_Page#actionEdit']);
			
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_UPDATED,
				XenForo_Link::buildPublicLink('pages', $page)
			);
		}
		else
		{
			$nodeBreadCrumbs = $this->_getNodeModel()->getNodeBreadCrumbs($page, false);
			
			$template = $templateModel->getTemplateInStyleByTitle($pageModel->getTemplateTitle($page));
			
			$attachmentParams = array(
				'hash' => md5(uniqid('', true)),
				'content_type' => 'pageeditor_page',
				'content_data' => array(
					'node_id' => $page['node_id'],
				)
			); 
			$attachments = $attachmentModel->getAttachmentsByContentId('pageeditor_page', $page['node_id']);
	
			$viewParams = array(
				'page' => $page,
				'nodeBreadCrumbs' => $nodeBreadCrumbs,
			
				'attachmentParams' => $attachmentParams,
				'attachments' => $attachmentModel->prepareAttachments($attachments),
				'attachmentConstraints' => $attachmentModel->getAttachmentConstraints(),
			
				'template' => $template,
			);
	
			return $this->responseView(
				'PageEditor_ViewPublic_Page_Edit',
				'pageeditor_page_edit',
				$viewParams
			);
		}
	}
	
	public function PageEditor_actionEdit(XenForo_DataWriter_Page $dw)
	{
		$attachmentHash = $this->_input->filterSingle('attachment_hash', XenForo_Input::STRING);
		$dw->setExtraData(PageEditor_XenForo_DataWriter_Page::DATA_ATTACHMENT_HASH, $attachmentHash);
	}
	
	protected function _getPageOrError($nodeName)
	{
		$page = parent::_getPageOrError($nodeName);
		
		if (is_array($page) AND !empty($page['node_id']))
		{
			$page['PageEditor_canEdit'] = $this->_getPageModel()->PageEditor_canEditPage($page);
		}
		
		return $page;
	}
}