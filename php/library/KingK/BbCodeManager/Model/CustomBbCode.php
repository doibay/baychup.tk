<?php

/**
 * Model for BB code related behaviors.
 *
 * @package KingK_BbCodeManager
 */
class KingK_BbCodeManager_Model_CustomBbCode extends XenForo_Model
{	
	/**
	 * Gets the specified BB code.
	 *
	 * @param string $id
	 *
	 * @return array|false
	 */
	public function getCustomBbCodeById($id)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM kingk_bbcm
			WHERE tag = ?
		', $id);
	}

	/**
	 * Gets all BB Code, ordered by tag.
	 *
	 * @return array [site id] => info
	 */
	public function getAllCustomBbCodes($cmd = null)
	{
		$bbcodes = $this->fetchAllKeyed('
				SELECT *
				FROM kingk_bbcm
				ORDER BY tag
			', 'tag');
		
		//Strict command will exclude 'buttons without bbcode function' (starting with '@') - usefull for the Bb Codes Help page
		if(!empty($cmd) && $cmd == 'strict')
		{
			foreach ($bbcodes as $key => $bbcode)
			{
				if($bbcode['tag'][0] == '@')
				{
					unset($bbcodes[$key]);
				}
			}	
		}
		
		return $bbcodes;
	}

	public function getAllActiveCustomBbCodes($cmd = null)
	{
		$active = $this->fetchAllKeyed('
			SELECT *
			FROM kingk_bbcm
			WHERE active = \'1\'
			ORDER BY tag
		', 'tag');
		
		foreach($active AS $tag)
		{
			$active[$tag['tag']]['tag'] = strtoupper($tag['tag']);
			$active[$tag['tag']]['theOptions'] = '';
			
			for($i = 0; $i < $tag['numberOfOptions']; $i++)
			{
				$active[$tag['tag']]['theOptions'] .= 'option';
				if(($i + 1) != $tag['numberOfOptions'])
				{
					$active[$tag['tag']]['theOptions'] .= ', ';
				}
			}
		}
		
		//Strict command will exclude 'buttons without bbcode function' (starting with '@') - usefull for the Bb Codes Help page
		if(!empty($cmd) && $cmd == 'strict')
		{
			foreach ($active as $key => $bbcode)
			{
				if($bbcode['tag'][0] == '@')
				{
					unset($active[$key]);
				}
			}	
		}
		
		return $active;
	}

	/* *
	*	Cache a simple list of all active tags available inside the simple cache
	*	Not use for buttons but usefull for other addons;
	*	Ref: http://xenforo.com/community/threads/what-is-the-best-way-to-add-datas-into-the-cache.30814/#post-352012
	*/
	public function SimpleCachedActiveCustomBbCodes()
	{
		$active = $this->fetchAllKeyed('
			SELECT tag
			FROM kingk_bbcm
			WHERE active = \'1\'
			ORDER BY tag
		', 'tag');
		
		if(is_array($active))
		{
			$string = '';
			foreach ($active as $bbcode)
			{
				 $string .= $bbcode['tag'].',';
			}
			
			$string =  substr($string, 0, -1);
	
			XenForo_Application::setSimpleCacheData('kingk_bbcm_active', $string);
		}
	}

	public function WipeActiveBbCodesSimpleCache()
	{
		XenForo_Application::setSimpleCacheData('kingk_bbcm_active', false);
	}

	/**
	* 	Get bbcodes with a button option
	*/    
	public function getBbCodesWithButton()
	{
	return $this->fetchAllKeyed('
			SELECT tag, active, hasButton, button_has_usr, button_usr, killCmd, custCmd, imgMethod, buttonDesc, tagOptions, tagContent
			FROM kingk_bbcm
			WHERE hasButton = \'1\'
			ORDER BY tag ASC	
		', 'tag');
	}

	/**
	* 	Get usergroups and return selected ones
	*/    
	public function getUserGroupOptions($selectedUserGroupIds)
        {
		$userGroups = array();
		foreach ($this->getDbUserGroups() AS $userGroup)
		{
			$userGroups[] = array(
			'label' => $userGroup['title'],
			'value' => $userGroup['user_group_id'],
			'selected' => in_array($userGroup['user_group_id'], $selectedUserGroupIds)
			);
		}
		
		return $userGroups;
        }

	/**
	* 	Get all usergroups (works with the above function)
	*/ 
	public function getDbUserGroups()
        {
		return $this->_getDb()->fetchAll('
			SELECT user_group_id, title
			FROM xf_user_group
			WHERE user_group_id
			ORDER BY user_group_id
		');
        }

	
	public function getBbCodeXml(array $bbcode)
	{
		$document = new DOMDocument('1.0', 'utf-8');
		$document->formatOutput = true;

		$rootNode = $document->createElement('bbcode');
		$document->appendChild($rootNode);

		$rootNode->appendChild($document->createElement('tag', $bbcode['tag']));
		$rootNode->appendChild($document->createElement('title', $bbcode['title']));
		$rootNode->appendChild($document->createElement('description', $bbcode['description']));

		$replacement = $rootNode->appendChild($document->createElement('replacement'));
		$replacementBegin = $replacement->appendChild($document->createElement('begin', ''));
		$replacementBegin->appendChild($document->createCDATASection($bbcode['replacementBegin']));
		$replacementEnd = $replacement->appendChild($document->createElement('end', ''));
		$replacementEnd->appendChild($document->createCDATASection($bbcode['replacementEnd']));

		$phpcallback = $rootNode->appendChild($document->createElement('phpcallback'));
		$phpcallback->appendChild($document->createElement('class', $bbcode['phpcallback_class']));
		$phpcallback->appendChild($document->createElement('method', $bbcode['phpcallback_method']));
		
		$example = $rootNode->appendChild($document->createElement('example', ''));
		$example->appendChild($document->createCDATASection($bbcode['example']));
		
		$rootNode->appendChild($document->createElement('active', $bbcode['active']));
		$rootNode->appendChild($document->createElement('requiresOption', $bbcode['requiresOption']));
		$rootNode->appendChild($document->createElement('advancedOptions', $bbcode['advancedOptions']));
		$rootNode->appendChild($document->createElement('numberOfOptions', $bbcode['numberOfOptions']));
		$rootNode->appendChild($document->createElement('regex', $bbcode['regex']));
		$rootNode->appendChild($document->createElement('trimLeadingLinesAfter', $bbcode['trimLeadingLinesAfter']));
		$rootNode->appendChild($document->createElement('plainCallback', $bbcode['plainCallback']));
		$rootNode->appendChild($document->createElement('plainChildren', $bbcode['plainChildren']));
		$rootNode->appendChild($document->createElement('stopSmilies', $bbcode['stopSmilies']));
		$rootNode->appendChild($document->createElement('stopLineBreakConversion', $bbcode['stopLineBreakConversion']));
		$rootNode->appendChild($document->createElement('hasButton', $bbcode['hasButton']));
		$rootNode->appendChild($document->createElement('button_has_usr', $bbcode['button_has_usr']));
		$rootNode->appendChild($document->createElement('button_usr', $bbcode['button_usr']));		
		$rootNode->appendChild($document->createElement('killCmd', $bbcode['killCmd']));
		$rootNode->appendChild($document->createElement('custCmd', $bbcode['custCmd']));
		$rootNode->appendChild($document->createElement('imgMethod', $bbcode['imgMethod']));
		$rootNode->appendChild($document->createElement('buttonDesc', $bbcode['buttonDesc']));
		$rootNode->appendChild($document->createElement('tagOptions', $bbcode['tagOptions']));
		$rootNode->appendChild($document->createElement('tagContent', $bbcode['tagContent']));


		return $document;
	}

}