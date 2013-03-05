<?php

class PageEditor_XenForo_Model_Page extends XFCP_PageEditor_XenForo_Model_Page
{
	public function PageEditor_canEditPage(array $page, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReferenceForNode($page['node_id'], $viewingUser, $nodePermissions);

		return XenForo_Permission::hasContentPermission($nodePermissions, 'PageEditor_edit');
	}
}