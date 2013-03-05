<?php

class PageEditor_Listener
{
	public static function load_class($class, array &$extend)
	{
		static $classes = array(
			'XenForo_BbCode_Formatter_Wysiwyg',	
		
			'XenForo_ControllerPublic_Page',
		
			'XenForo_DataWriter_Page',
		
			'XenForo_Model_Page',
		
			'XenForo_ViewPublic_Page_View',
		);
		
		if (in_array($class, $classes))
		{
			$extend[] = 'PageEditor_' . $class;
		}
	}
	
	public static function template_create($templateName, array &$params, XenForo_Template_Abstract $template)
	{
		if ($templateName == 'pagenode_container')
		{
			$template->preloadTemplate('pageeditor_edit_page_button');
		}
	}
	
	public static function template_post_render($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template)
	{
		if ($templateName == 'pagenode_container')
		{
			$buttonTemplate = $template->create('pageeditor_edit_page_button', $template->getParams());
			$buttonHtml = $buttonTemplate->render();
			$buttonHtml = utf8_trim($buttonHtml);
			
			if (!empty($buttonHtml))
			{
				if (!isset($containerData['topctrl'])) $containerData['topctrl'] = '';
				
				$containerData['topctrl'] .= $buttonHtml;
			}
		}
		else
		{
			PageEditor_Renderer::render($templateName, $content, $containerData, $template);
		}
	}
	
	public static function file_health_check(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
	{
		$hashes += PageEditor_FileSums::getHashes();
	}
}