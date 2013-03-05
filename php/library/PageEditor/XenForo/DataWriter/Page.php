<?php

class PageEditor_XenForo_DataWriter_Page extends XFCP_PageEditor_XenForo_DataWriter_Page
{
	const DATA_ATTACHMENT_HASH = 'attachmentHash';
	
	protected function _getFields()
	{
		$fields = parent::_getFields();
		
		$fields['xf_page']['pageeditor_attach_count'] = array('type' => XenForo_DataWriter::TYPE_UINT, 'default' => 0);
		
		return $fields;
	}
	
	protected function _preSave()
	{
		if (isset($GLOBALS['PageEditor_XenForo_ControllerPublic_Page#actionEdit']))
		{
			$GLOBALS['PageEditor_XenForo_ControllerPublic_Page#actionEdit']->PageEditor_actionEdit($this);
		}
		
		return parent::_preSave();
	}
	
	protected function _postSave()
	{
		$attachmentHash = $this->getExtraData(self::DATA_ATTACHMENT_HASH);
		if ($attachmentHash)
		{
			$this->_PageEditor_associateAttachments($attachmentHash);
		}
		
		return parent::_postSave();
	}
	
	protected function _postDelete()
	{
		if ($this->get('pageeditor_attach_count'))
		{
			$this->_PageEditor_deleteAttachments();
		}
		
		return parent::_postDelete();
	}
	
	protected function _PageEditor_associateAttachments($attachmentHash)
	{
		$rows = $this->_db->update('xf_attachment', array(
			'content_type' => 'pageeditor_page',
			'content_id' => $this->get('node_id'),
			'temp_hash' => '',
			'unassociated' => 0
		), 'temp_hash = ' . $this->_db->quote($attachmentHash));
		
		if ($rows)
		{
			$this->set('pageeditor_attach_count', $this->get('pageeditor_attach_count') + $rows, '', array('setAfterPreSave' => true));
			
			$this->_db->update('xf_page', array(
				'pageeditor_attach_count' => $this->get('pageeditor_attach_count')
			), 'node_id = ' .  $this->_db->quote($this->get('node_id')));
		}
	}
	
	protected function _PageEditor_deleteAttachments()
	{
		$this->getModelFromCache('XenForo_Model_Attachment')->deleteAttachmentsFromContentIds(
			'pageeditor_page',
			array($this->get('node_id'))
		);
	}
}