<?php

class KingK_BbCodeManager_DataWriter_CustomBbCodes extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'bbcm_bbCodeNotFound';
	protected $_xenBbCodes = array('b', 'i', 'u', 's', 'color', 'font', 'size', 'left', 'center', 'right', 'indent', 'url', 'email', 'img', 'quote', 'code', 'php', 'html', 'list', 'plain', 'media', 'attach');

	protected function _getFields()
	{
		return array(
			'kingk_bbcm' => array(
				'tag' => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 25,
						'verification' => array('$this', '_verifyBbCodeId'),
						'requiredError' => 'bbcm_errorInvalidId'
				),
				'title'    => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50,
						 'requiredError' => 'please_enter_valid_title'
				),
				'description'      => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 250,
						'requiredError' => 'bbcm_pleaseEnterValidDesc'
				),
				'replacementBegin'    => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 250,
				),
				'replacementEnd'    => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 250,
				),
				'phpcallback_class'    => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 250,
				),
				'phpcallback_method'    => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 250,
				),
				'example'    => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 2000,
						'requiredError' => 'please_enter_embed_html'
				),
				'active'    => array('type' => self::TYPE_UINT, 'default' => 1),
				'requiresOption'    => array('type' => self::TYPE_UINT, 'default' => 0),
				'advancedOptions'    => array('type' => self::TYPE_UINT, 'default' => 0),
				'numberOfOptions'    => array('type' => self::TYPE_UINT, 'default' => 0),
				'regex' => array('type' => self::TYPE_STRING, 'default' => ''),
				'trimLeadingLinesAfter' => array('type' => self::TYPE_UINT, 'default' => 0, 'maxLength' => 1),
				'plainCallback' => array('type' => self::TYPE_UINT, 'default' => 0),
				'plainChildren' => array('type' => self::TYPE_UINT, 'default' => 0),
				'stopSmilies' => array('type' => self::TYPE_UINT, 'default' => 0),
				'stopLineBreakConversion' => array('type' => self::TYPE_UINT, 'default' => 0),
				'hasButton' 	=> array(
						'type' => self::TYPE_UINT,
						'default' => 0
				),
				'button_has_usr' 	=> array(
						'type' => self::TYPE_UINT,
						'default' => 0
				),
				'button_usr' 	=> array(
						'type' => self::TYPE_STRING, 
						'default' => ''						
				),
				'killCmd' 	=> array(
						'type' => self::TYPE_UINT,
						'default' => 0
				),
				'custCmd' 	=> array(
						'type' => self::TYPE_STRING, 
						'default' => '',
						'maxLength' => 50

				),				
				'imgMethod' 	=> array(
						'type' => self::TYPE_STRING, 
						'default' => '',
						'maxLength' => 20

				),
				'buttonDesc' 	=> array(
						'type' => self::TYPE_STRING, 
						'default' => '',
						'maxLength' => 255

				),
				'tagOptions' 	=> array(
						'type' => self::TYPE_STRING, 
						'default' => '',
						'maxLength' => 255

				),
				'tagContent' 	=> array(
						'type' => self::TYPE_STRING, 
						'default' => '',
						'maxLength' => 255

				)				
				
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data, 'tag'))
		{
			return false;
		}

		return array('kingk_bbcm' => $this->_getCustomBbCodeModel()->getCustomBbCodeById($id));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'tag = ' . $this->_db->quote($this->getExisting('tag'));
	}

	protected function _verifyBbCodeId(&$tagId)
	{
		$tag = strtolower($tagId);

		if (preg_match('/[^a-zA-Z0-9_@]/', $tagId))
		{
			$this->error(new XenForo_Phrase('please_enter_an_id_using_only_alphanumeric'), 'tag');
			return false;
		}
		
		if(in_array($tagId, $this->_xenBbCodes))
		{
			$this->error(new XenForo_Phrase('bbcm_errorTagMustBeUnique'), 'tag');
			return false;
		}

		// This currently fails. Be careful when added BB Codes! 
		if ($this->isInsert() || $tag != $this->getExisting('tag'))
		{
			$existing = $this->_getCustomBbCodeModel()->getCustomBbCodeById($tagId);
			if($existing)
			{
				$this->error(new XenForo_Phrase('bbcm_errorTagMustBeUnique'), 'tag');
				return false;
			}
		}

		return true;
	}
	
	protected function _verifyCallback($class, $method)
	{
            if (!XenForo_Application::autoload($class) || !method_exists($class, $method))
            {
                $this->error(new XenForo_Phrase('please_enter_valid_callback_method'), 'callback_method');
            }
	}
	
	protected function _preSave()
	{
		if($this->get('phpcallback_class') == '' && $this->get('phpcallback_method') == '' && $this->get('replacementBegin') == '' && $this->get('replacementEnd') == '')
		{
			$this->error(new XenForo_Phrase('bbcm_errorEnterAReplacementMethod'), 'tag');
		}
		
		if($this->get('replacementBegin') && $this->get('phpcallback_method'))
		{
			$this->error(new XenForo_Phrase('bbcm_errorEnterOneReplacementMethod'), 'tag');
			return false;
		}
		
		if($this->get('replacementBegin') && $this->get('phpcallback_class'))
		{
			$this->error(new XenForo_Phrase('bbcm_errorEnterOneReplacementMethod'), 'tag');
			return false;
		}
		
		if($this->get('replacementEnd') && $this->get('phpcallback_class'))
		{
			$this->error(new XenForo_Phrase('bbcm_errorEnterOneReplacementMethod'), 'tag');
			return false;
		}
		
		if($this->get('replacementEnd') && $this->get('phpcallback_method'))
		{
			$this->error(new XenForo_Phrase('bbcm_errorEnterOneReplacementMethod'), 'tag');
			return false;
		}
		
		if($this->get('phpcallback_class'))
		{
			$this->_verifyCallback($this->get('phpcallback_class'), $this->get('phpcallback_method'));
		}
	}
	
	protected function _getCustomBbCodeModel()
	{
		return $this->getModelFromCache('KingK_BbCodeManager_Model_CustomBbCode');
	}
}