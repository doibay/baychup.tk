<?php

class PageEditor_AttachmentHandler_Page extends XenForo_AttachmentHandler_Abstract
{
	protected $_pageModel = null;

	protected $_contentIdKey = 'node_id';
	protected $_contentRoute = 'pages';
	protected $_contentTypePhraseKey = 'page';

	protected function _canUploadAndManageAttachments(array $contentData, array $viewingUser)
	{
		$pageModel = $this->_getPageModel();

		if (!empty($contentData['node_id']))
		{
			$page = $pageModel->getPageById($contentData['node_id'], array(
				'permissionCombinationId' => $viewingUser['permission_combination_id']
			));
			
			if ($page)
			{
				$permissions = XenForo_Permission::unserializePermissions($page['node_permission_cache']);
				
				return (
					$pageModel->canViewPage($page, $null, $permissions, $viewingUser)
					&& $pageModel->PageEditor_canEditPage($page, $null, $permissions, $viewingUser)
				);
			}
		}

		return false; // invalid content data
	}

	protected function _canViewAttachment(array $attachment, array $viewingUser)
	{
		$pageModel = $this->_getPageModel();

		$page = $pageModel->getPageById($attachment['content_id'], array(
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));
		if (!$page)
		{
			return false;
		}

		$permissions = XenForo_Permission::unserializePermissions($page['node_permission_cache']);

		return $pageModel->canViewPage($page, $null, $permissions, $viewingUser);
	}

	public function attachmentPostDelete(array $attachment, Zend_Db_Adapter_Abstract $db)
	{
		$db->query('
			UPDATE xf_page
			SET pageeditor_attach_count = IF(pageeditor_attach_count > 0, pageeditor_attach_count - 1, 0)
			WHERE node_id = ?
		', $attachment['content_id']);
	}

	/**
	 * @return XenForo_Model_Page
	 */
	protected function _getPageModel()
	{
		if (!$this->_pageModel)
		{
			$this->_pageModel = XenForo_Model::create('XenForo_Model_Page');
		}

		return $this->_pageModel;
	}
}