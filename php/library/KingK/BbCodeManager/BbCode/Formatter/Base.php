<?php

class KingK_BbCodeManager_BbCode_Formatter_Base extends XFCP_KingK_BbCodeManager_BbCode_Formatter_Base
{
	protected $_tags;
	protected $_customTags = null;
	
	public function getTags()
	{
		$this->_tags = parent::getTags();

		if ($this->_customTags !== null)
		{
			return $this->_tags;
		}

		$this->_customTags = XenForo_Model::create('KingK_BbCodeManager_Model_CustomBbCode')->getAllCustomBbCodes('strict');
		 
		if(!is_array($this->_customTags))
		{
			return $this->_tags;
		}

		foreach($this->_customTags AS $custom)
		{
			$optionsArray = array();
			if((boolean)$custom['active'])
			{
	      			if($custom['replacementBegin'])
	      			{
	      				if($custom['advancedOptions'])
	      				{
	      					$optionsArray['hasOption'] = true;
	      					if($custom['plainCallback'])
	      					{
	      						$optionsArray['parseCallback'] = array($this, 'parseValidatePlainIfNoOption');
	      					}
	      					$optionsArray['callback'] = array($this, 'renderAdvancedOptionsTag');
	      					//ADD THE FOLLOWING DATAS DURING THE TAG CONSTRUCTION
	      					$optionsArray['numberOfOptions'] = $custom['numberOfOptions'];
	      					$optionsArray['replacementBegin'] = $custom['replacementBegin'];
	      					$optionsArray['replacementEnd'] = $custom['replacementEnd'];
	      				}
	      				else
	      				{
	      					$optionsArray['hasOption'] = $this->_hasOption($custom['requiresOption']);
	      					$optionsArray['replace'] = array($custom['replacementBegin'], $custom['replacementEnd']);
	      				}
	      			}
	      			else if($custom['phpcallback_class'])
	      			{
	      				if($custom['plainCallback'])
	      				{
	      					$optionsArray['parseCallback'] = array($this, 'parseValidatePlainIfNoOption');
	      				}
	      				$optionsArray['callback'] = array($this, 'renderAdvancedTag');
	      				//ADD CLASS AND METHOD DURING THE TAG CONSTRUCTION
	      				$optionsArray['phpcallback_class'] = $custom['phpcallback_class'];
	      				$optionsArray['phpcallback_method'] = $custom['phpcallback_method'];
	      			}
	      			
	      			if($custom['trimLeadingLinesAfter'] > 0 && $custom['trimLeadingLinesAfter'] < 3)
	      			{
	      				$optionsArray['trimLeadingLinesAfter'] = $custom['trimLeadingLinesAfter'];
	      			}
	      			
	      			if($custom['regex'] != '')
	      			{
	      				$optionsArray['optionRegex'] = $custom['regex'];
	      			}
	      			
	      			if($custom['plainChildren'])
	      			{
	      				$optionsArray['plainChildren'] = true;
	      			}
	      			
	      			if($custom['stopSmilies'])
	      			{
	      				$optionsArray['stopSmilies'] = true;
	      			}
	      			
	      			if($custom['stopLineBreakConversion'])
	      			{
	      				$optionsArray['stopLineBreakConversion'] = true;
	      			}
	      							
	      			$this->_tags[$custom['tag']] = $optionsArray;
	      		}
		}
		return $this->_tags;
	}
	
	protected function _hasOption($has)
	{
		if($has == 0)
		{
			return false;
		}
		return true;
	}
	
	public function parseMultipleOptions($tag)
	{
		$attributes = explode(', ', $tag);
		return $attributes;
	}
	
	public function renderAdvancedOptionsTag(array $tag, array $rendererStates)
	{

		//Retrieve data from $_tags
		$tagInfo['numberOfOptions'] = $this->_tags[$tag['tag']]['numberOfOptions'];
		$tagInfo['replacementBegin'] = $this->_tags[$tag['tag']]['replacementBegin']; 
		$tagInfo['replacementEnd'] = $this->_tags[$tag['tag']]['replacementEnd']; 
		
		/*
			No need anymore to access database
		$tagInfo = XenForo_Model::create('KingK_BbCodeManager_Model_CustomBbCode')->getCustomBbCodeById($tag['tag']);
		*/

		if (!empty($tag['option']) && $this->parseMultipleOptions($tag['option']))
		{
			$options = $this->parseMultipleOptions($tag['option']);
			if((int)count($options) == $tagInfo['numberOfOptions'])
			{
				for($replaceIndex = 0; $replaceIndex <= $tagInfo['numberOfOptions']; $replaceIndex++)
				{
					$replaceNumber = $replaceIndex + 1;
					$tagInfo['replacementBegin'] = str_replace('{' . $replaceNumber . '}', $options[$replaceIndex], $tagInfo['replacementBegin']);
					$tagInfo['replacementEnd'] = str_replace('{' . $replaceNumber . '}', $options[$replaceIndex], $tagInfo['replacementEnd']);
					
					$content = $this->renderSubTree($tag['children'], $rendererStates);
				}
			 	return $tagInfo['replacementBegin'] . $content . $tagInfo['replacementEnd'];
			}
			else
			{
				return $tag['original'][0] . $tag['children'][0] . $tag['original'][1];
			}
		}
	}
	
	public function renderAdvancedTag(array $tag, array $rendererStates)
	{
		//Retrieve phpcallback class & method from $_tags
		$phpcallback_class = $this->_tags[$tag['tag']]['phpcallback_class'];
		$phpcallback_method = $this->_tags[$tag['tag']]['phpcallback_method'];
		
		/*
			No need anymore to access database
		$tagInfo = XenForo_Model::create('KingK_BbCodeManager_Model_CustomBbCode')->getCustomBbCodeById($tag['tag']);
		*/
		
		XenForo_Application::autoload($phpcallback_class);
		return call_user_func_array(array($phpcallback_class, $phpcallback_method), array($tag, $rendererStates, &$this));
	}
}