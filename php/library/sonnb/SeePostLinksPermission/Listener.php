<?php

/**
 * Product: sonnb - See post's links permission
 * Version: 1.1.2
 * Date: 28th Jan 2013
 * Author: sonnb
 * Website: www.sonnb.com - www.UnderWorldVN.com
 * License: You might not copy or redistribute this addon.
 */
class sonnb_SeePostLinksPermission_Listener
{

    public static function load_class($class, &$extend)
    {
    	switch ($class)
    	{
    		case 'XenForo_ControllerPublic_Post':
    			$extend[] = 'sonnb_SeePostLinksPermission_ControllerPublic_Post';
    			break;
    		case 'XenForo_ControllerPublic_Thread':
    			$extend[] = 'sonnb_SeePostLinksPermission_ControllerPublic_Thread';
    			break;
    			
    		case 'XenForo_ViewPublic_Thread_View':
    			$extend[] = 'sonnb_SeePostLinksPermission_ViewPublic_Thread_View';
    			break;
    		case 'XenForo_ViewPublic_Thread_ViewPosts':
    			$extend[] = 'sonnb_SeePostLinksPermission_ViewPublic_Thread_ViewPosts';
    			break;
    		case 'XenForo_ViewPublic_Thread_ViewNewPosts':
    			$extend[] = 'sonnb_SeePostLinksPermission_ViewPublic_Thread_ViewNewPosts';
    			break;
    	}
    }
    
    public static function tms_like_class(&$templateText, &$applyCount, $styleId)
    {    	
    	$ptn = '#<xen:if is="\{\$post\.canLike\}">(.*?)</xen:if>#si';
    	
    	$count = preg_match($ptn, $templateText, $match);
    	
    	if ($count)
    	{
    		$replace = str_replace('LikeLink', 'LikeLinkHide', $match[0]);
    	
    		$templateText = str_replace($match[0], $replace, $templateText);
    	}
    }

    public static function renderUserOptions(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        $preparedOption['formatParams'] = XenForo_Option_UserGroupChooser::getUserGroupOptions(
                        $preparedOption['option_value']
        );

        return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
                        'option_list_option_checkbox', $view, $fieldPrefix, $preparedOption, $canEdit
        );
    }
    
    public static function renderNodes(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        $preparedOption['formatParams'] = self::getNodeOptions(
                        $preparedOption['option_value']
        );

        return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
                        'sonnbPostLinksPermission_Nodes', $view, $fieldPrefix, $preparedOption, $canEdit
        );
    }

    public static function getNodeOptions($selectedForum, $includeRoot = false)
    {
        $nodeModel = XenForo_Model_DataRegistry::create('XenForo_Model_Node');

        $options = array();

        foreach($nodeModel->getAllNodes() AS $nodeId=>$node)
        {
            $node['depth'] += (($includeRoot && $nodeId) ? 1 : 0);

            $options[$nodeId] = array(
                'value'       =>$nodeId,
                'label'       =>$node['title'],
                'selected'    =>in_array($nodeId, $selectedForum),
                'depth'       =>$node['depth'],
                'node_type_id'=>$node['node_type_id']
            );
        }

        return $options;
    }

}