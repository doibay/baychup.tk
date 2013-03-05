<?php

class PageEditor_Renderer
{
	protected static $_data = array();
	
	public static function preparePageTemplate($templateTitle, array $page, XenForo_View $view)
	{
		self::$_data[$templateTitle] = array(
			'page' => $page,
			'view' => $view,
		);
	}
	
	public static function render($templateTitle, &$content, array &$containerData, XenForo_Template_Abstract $template)
	{
		if (!isset(self::$_data[$templateTitle])) return;
		
		$page = self::$_data[$templateTitle]['page'];
		$view = self::$_data[$templateTitle]['view'];
		
		if (!empty($page['pageeditor_attach_count']))
		{
			$attachmentModel = XenForo_Model::create('XenForo_Model_Attachment');
	
			$attachments = $attachmentModel->getAttachmentsByContentId('pageeditor_page', $page['node_id']);
			foreach (array_keys($attachments) as $attachmentId)
			{
				$attachments[$attachmentId] = $attachmentModel->prepareAttachment($attachments[$attachmentId]);
			}

			$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('PageEditor_BbCode_Formatter_ToDisplay', array('view' => $view)));
			$bbCodeStates = array(
				'attachments' => $attachments,
				'viewAttachments' => true, // if a user can view the page, attachment is visible too
			);
			
			$content = '<div class="ugc baseHtml">' . strval(new XenForo_BbCode_TextWrapper($content, $bbCodeParser, $bbCodeStates)) . '</div>';
		}
	}
}