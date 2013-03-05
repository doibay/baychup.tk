<?php

class XenTagMod_Listener_RepTemps
{
    public static function templateCreate($templateName, array &$params, XenForo_Template_Abstract $template)
    {
		$options = Xenforo_Application::get('options');
		
        if ($templateName == 'tinhte_xentag_sidebar_cloud' && $options->xentag_mod_enable_sidebar)
        {
            $templateName = 'xentag_sidebar_cloud_mod';
        }
		
		if ($templateName == 'tinhte_xentag_widget_cloud' && $options->xentag_mod_enable_sidebar)
        {
            $templateName = 'xentag_widget_cloud_mod';
        }
		
		if ($templateName == 'tinhte_xentag_tag_list' && $options->xentag_mod_enable_main)
        {
            $templateName = 'xentag_tag_list_mod';
        }
    }
	
	public static function template_hook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template) {
		if ($hookName == 'xentag_tag_cloud_item_mod') {
			// our special hook to populate data to the sidebar
			// doing this will make it super-easy to use the sidebar template
			// just put the include statement in the target page and you are done!
			// <xen:include template="tinhte_xentag_sidebar_cloud" />
			// supported parameters:
			// - max: maximum number of links
			$tagModel = XenForo_Model::create('Tinhte_XenTag_Model_Tag');
			
			$conditions = array();
			$fetchOptions = array(
				'order' => 'content_count',
				'direction' => 'desc',
				'limit' => isset($hookParams['max']) ? $hookParams['max'] : Tinhte_XenTag_Option::get('cloudMax'),
			);
			
			$tags = $tagModel->getAllTag($conditions, $fetchOptions);
			$tagModel->calculateCloudLevel($tags);
			$results = '';
			
			foreach ($tags as $tag) {
				$search = array('{TAG_TEXT}', '{TAG_LINK}', '{TAG_CONTENT_COUNT}', '{TAG_LEVEL}');
				$tag_resized = XenForo_Template_Helper_Core::styleProperty('xentag_mod_sidebar_size_'.$tag['cloudLevel']);
				$replace = array(
					htmlspecialchars($tag['tag_text']),
					XenForo_Link::buildPublicLink(Tinhte_XenTag_Option::get('routePrefix'), $tag),
					XenForo_Template_Helper_Core::numberFormat($tag['content_count']),
					$tag_resized,
				);
				$results .= str_replace($search, $replace, $contents);
			}
			
			$contents = $results; // switch the template contents with our html
		}
	}
}