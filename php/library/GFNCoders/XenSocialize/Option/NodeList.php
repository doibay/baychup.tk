<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Option_NodeList
{
	public static function fetchOptions(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{		
		$editLink = $view->createTemplateObject('option_list_option_editlink', array(
				'preparedOption' => $preparedOption,
				'canEditOptionDefinition' => $canEdit
			));
		
		$nodeModel = XenForo_Model::create('XenForo_Model_Node');
		
		$options = self::_prepareNodesForDisplay($nodeModel->getAllNodes(), $preparedOption['option_value']);
		
		return $view->createTemplateObject('GFNXenSocialize_option_multiselect', array(
				'fieldPrefix' => $fieldPrefix,
				'listedFieldName' => $fieldPrefix . '_listed[]',
				'preparedOption' => $preparedOption,
				'formatParams' => $options,
				'editLink' => $editLink
			));
	}
	
	protected static function _prepareNodesForDisplay(array $nodes, $selectedNodeId = 0, $includeRoot = false)
	{
		$options = array();
		
		if ($includeRoot !== false)
		{
			$root = $this->getRootNode();

			$options[0] = array(
				'value' => 0,
				'label' => (is_string($includeRoot) === true ? $includeRoot : $root['title']),
				'selected' => (strval($selectedNodeId) === '0'),
				'depth' => 0,
			);
		}

		foreach ($nodes AS $nodeId => $node)
		{
			$node['depth'] += (($includeRoot && $nodeId) ? 1 : 0);
		
			$options[$nodeId] = array(
					'value' => $nodeId,
					'label' => $node['title'],
					'selected' => ($nodeId == $selectedNodeId),
					'depth' => $node['depth'],
					'disabled' => $node['node_type_id'] == 'Forum' ? false : true
			);
		}
		
		return $options;
	}
}