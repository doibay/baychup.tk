<?php

class KingK_BbCodeManager_ControllerAdmin_CustomBbCodes extends XenForo_ControllerAdmin_Abstract
{
	private static $protectedXenCmd = 
		array('justifyright','justifycenter','justifyleft','indent','outdent','redo','undo',
		'removeformat','fontselect','fontsizeselect','forecolor','xenforo_smilies','bold','italic',
		'underline','strikethrough','bullist','numlist','link','unlink','image','xenforo_media',
		'xenforo_code','xenforo_custom_bbcode');

	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('customBBCodes');
	}

	public function actionIndex()
	{
		$codes = $this->_getCustomBBCodeModel()->getAllCustomBBCodes();
		
		//Add class
		if(is_array($codes))
		{
			foreach ($codes as &$code)
			{
				if($code['tag'][0] == '@')
				{
					$code['class'] = 'orphanButton';
				}
				else
				{
					$code['class'] = 'normalButton';			
				}
			}
		}
		
		$viewParams = array(
			'codes' => $codes,
			'customBBCodes' => XenForo_Visitor::getInstance()->hasAdminPermission('customBBCodes')
 		);
		return $this->responseView('XenForo_ViewAdmin_Custom_Bb_Codes_List', 'custom_bb_codes_list', $viewParams);
	}

	protected function _getCustomBbCodeAddEditResponse(array $code)
	{
		//Check if the edit is made from the button manager
		if (isset($_GET['bm']))
		{
			$code['bm_src'] = $_GET['bm'];
		}

		if(isset($code['button_usr']) AND !empty($code['button_usr'])) //Error to correct in Markitup!
		{
			//Usergroups Management: unserialize datas from db
			$code['button_usr'] = unserialize($code['button_usr']);
		}
		else
		{
			//If the config was blank, create an array
			$code['button_usr'] = array();
		}

		//Get the model to fetch datas and create a new variable for the "foreach in template"
		$code['usr_list'] = $this->_getCustomBBCodeModel()->getUserGroupOptions($code['button_usr'] );

		$viewParams = array(
			'code' => $code
		);

		return $this->responseView('XenForo_ViewAdmin_BbCodeMediaSite_Edit', 'custom_bb_codes_edit', $viewParams);
	}

	public function actionAdd()
	{
		return $this->_getCustomBbCodeAddEditResponse(array());
	}

	public function actionEdit()
	{
		$bbcodeId = $this->_input->filterSingle('tag', XenForo_Input::STRING);
		$code = $this->_getCustomBbCodeOrError($bbcodeId);

		return $this->_getCustomBbCodeAddEditResponse($code);
	}

	public function actionEnable()
	{
		$bbcodeId = $this->_input->filterSingle('tag', XenForo_Input::STRING);
		$this->_enableDisable($bbcodeId);
		
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('custom-bb-codes')
		);
	}
	
	public function actionDisable()
	{
		$bbcodeId = $this->_input->filterSingle('tag', XenForo_Input::STRING);
		$this->_enableDisable($bbcodeId);
		
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('custom-bb-codes')
		);
	}
	
	public function actionImportConfirm()
	{
		return $this->responseView('XenForo_ViewAdmin_AddOn_Install', 'custom_bb_codes_install');
	}
	
	public function actionSave()
	{
		$this->_assertPostOnly();

		//The tag field can be changed by the user... so it can be updated
		$tag = $this->_input->filterSingle('formtag', XenForo_Input::STRING);
		
		//The check tag can't be changed by the user... so it will be the reference if an update of the tag field occurs
		$checktag = $this->_input->filterSingle('checktag', XenForo_Input::STRING);

		//If the kill function is activated, check if the button name is not a XenForo button command name
		//Why? A bbcode button will automatically have a prefix (bbcm), but if the kill function (bypass), then no prefix anymore
		$killCmd = $this->_input->filterSingle('killCmd', XenForo_Input::UINT);
		
		if(!empty($killCmd) && (in_array($checktag, self::$protectedXenCmd) || in_array($tag, self::$protectedXenCmd)))
		{
			throw new XenForo_Exception(new XenForo_Phrase('bbcm_admin_error_cannot_use_protected_xen_cmd'), true);
		}

		$dwInput = $this->_input->filter(array(
				'tag' => XenForo_Input::STRING,
				'title' => XenForo_Input::STRING,
				'description' => XenForo_Input::STRING,
				'replacementBegin' => XenForo_Input::STRING,
				'replacementEnd' => XenForo_Input::STRING,
				'phpcallback_class' => XenForo_Input::STRING,
				'phpcallback_method' => XenForo_Input::STRING,
				'example' => XenForo_Input::STRING,
				'active' => XenForo_Input::UINT,
				'requiresOption' => XenForo_Input::UINT,
				'advancedOptions' => XenForo_Input::UINT,
				'numberOfOptions' => XenForo_Input::UINT,
				'regex' => XenForo_Input::STRING,
				'trimLeadingLinesAfter' => XenForo_Input::UINT,
				'plainCallback' => XenForo_Input::UINT,
				'plainChildren' => XenForo_Input::UINT,
				'stopSmilies' => XenForo_Input::UINT,
				'stopLineBreakConversion' => XenForo_Input::UINT,
				'hasButton' => XenForo_Input::UINT,
				'button_has_usr' => XenForo_Input::UINT,
				'killCmd' => XenForo_Input::UINT,
				'custCmd' => XenForo_Input::STRING,
				'imgMethod' => XenForo_Input::STRING,
				'buttonDesc' => XenForo_Input::STRING,
				'tagOptions' => XenForo_Input::STRING,
				'tagContent' => XenForo_Input::STRING
		));

		//ONLY BUTTON FIX (not bbcode)
		if( (!empty($tag)  && $tag[0] == '@') || (!empty($checktag) && $checktag[0] == '@') )
		{
			$dwInput['example'] = '#';
			$dwInput['replacementBegin'] = '#';
			$dwInput['replacementEnd'] = '#';			
		}
		
		//Array_keys is the only trick I've found to get the usergroups id selected... Associated template code => name="button_usr[{$list.value}]"
		$dwInput['button_usr'] = serialize(array_keys($this->_input->filterSingle('button_usr', array(XenForo_Input::STRING, 'array' => true))));

		$dw = XenForo_DataWriter::create('KingK_BbCodeManager_DataWriter_CustomBbCodes');
		if ($this->_getCustomBbCodeModel()->getCustomBbCodeById($checktag))
		{
			$dw->setExistingData($checktag);
			$this->_UpdateConfigsAfterBBcodeEditOrDelete($checktag, $dwInput);
		}
		$dw->bulkSet($dwInput);
		$dw->set('tag', $tag); //DND (Do Not Delete)
		$dw->save();

		//Update simple cache
		$this->_getCustomBbCodeModel()->SimpleCachedActiveCustomBbCodes();
		

		/***
			Return Manager
		***/
		$src = $this->_input->filterSingle('bmsrc', XenForo_Input::STRING);
		
		if(!empty($src))
		{
			//This is an edit from the Button Manager
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('custom-buttons/editorconfig' . $src)
			);			
		}

		//This is a standard edit
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('custom-bb-codes')
		);
		
		/* For debug
			return $this->responseMessage($dwInput['button_usr']);
		*/
	}

	/*****
	*	THE BRIDGE BETWEEN CUSTOM BBCODES & BUTTONS
	*	
	*	This function is a bridge from Custom BB codes modifications (save or edit) to the button config data
	*	@$button_tag
	*	@$new_values is only use when edit a previous button to give him a new value
	***/
	protected function _UpdateConfigsAfterBBcodeEditOrDelete($button_tag, array $new_values = null)
	{
		//Get rid of unnecessary datas
		unset($new_values['title'],$new_values['description'],$new_values['replacementBegin'],$new_values['replacementEnd'],
		$new_values['phpcallback_class'],$new_values['phpcallback_method'],$new_values['example'],$new_values['requiresOption'],
		$new_values['advancedOptions'],$new_values['numberOfOptions'],$new_values['regex'],$new_values['trimLeadingLinesAfter'],
		$new_values['plainCallback'],$new_values['plainChildren'],$new_values['stopSmilies'],$new_values['stopLineBreakConversion']);

		//Get all configs (rtl/ltr) to check if that button was used
		$config_all =  $this->_getCustomButtonsModel()->getAllConfig();
			
		foreach ($config_all as $config_id => $config)
		{
			//Only continue if the config was set & wasn't empty (for ie: user delete a default button before to have set a config)
			if(!isset($config['config_buttons_full']) || empty($config['config_buttons_full']))
			{
				continue;
			}
			
      			//Get back buttons full array
      			$config_buttons_full = unserialize($config['config_buttons_full']);
 
      			//Get the sub-array key (button key inside buttons config)
      			foreach ($config_buttons_full as $key => $selectedbutton)
      			{
      				if ($selectedbutton['tag'] == $button_tag) //Need to check in MarkItUp
      				{
      					$target = $key;
      				}
      			}
     		
      			//If the button has been found let's unset if the instruction is coming from the delete function OR update if it's only an update of an existing button
      			if(isset($target))
      			{
      				if(isset($new_values))
      				{
					//Update class key value
					if(!empty($new_values['active']))
					{
						$new_values['class'] = 'activeButton';
					}
					else
					{
						$new_values['class'] = 'unactiveButton';			
					}

      					//UPDATE FULL VALUES !!!
      					$config_buttons_full[$target] = $new_values;
      				}
      				else
      				{
      					//UNSET !!! 
      					unset($config_buttons_full[$target]);
      				}

      				//Let's serialize back the config
      				$config_buttons_full = serialize($config_buttons_full);
      			
      				//Before to write in the Database, let's also take back the button from the config_buttons_order table (string)
      				//If update, no need to change, the id remains the same
      				$config_buttons_order = $config['config_buttons_order'];
      				$config_buttons_order_array = explode(',', $config_buttons_order);
      				$target_key = array_search($button_tag, $config_buttons_order_array);

      				if(!isset($new_values))
      				{
      					//UNSET !!! 
      					unset($config_buttons_order_array[$target_key]);
      				}

      				$config_buttons_order = implode(',', $config_buttons_order_array);

      				//Let's write new config in the database
      				$dw = XenForo_DataWriter::create('KingK_BbCodeManager_DataWriter_CustomButtons');
      				if ($this->_getCustomButtonsModel()->getConfigById($config_id))
      				{
      					$dw->setExistingData($config_id);
      				}

      				$dw->set('config_buttons_order', $config_buttons_order);
      				$dw->set('config_buttons_full', $config_buttons_full);
      				$dw->save();
      
      				//Let's update the Registry
      				$this->_getCustomButtonsModel()->InsertConfigInRegistry();	
      			}
		}	
	}

	public function actionDelete()
	{
		//Get Bbcode tag
		$tag = $this->_input->filterSingle('tag', XenForo_Input::STRING);		

		if ($this->isConfirmedPost())
		{
			//Update button config
			$this->_UpdateConfigsAfterBBcodeEditOrDelete($tag);

			//Update simple cache
			$this->_getCustomBbCodeModel()->SimpleCachedActiveCustomBbCodes();

			return $this->_deleteData(
				'KingK_BbCodeManager_DataWriter_CustomBbCodes', 'tag',
				XenForo_Link::buildAdminLink('custom-bb-codes')
			);
		}
		else
		{
			$code = $this->_getCustomBbCodeOrError($tag);

			$viewParams = array(
				'code' => $code
			);
			return $this->responseView('XenForo_ViewAdmin_BbCodeMediaSite_Delete', 'custom_bb_codes_delete', $viewParams);
		}
	}
	
	public function actionImport()
	{
		$this->_assertPostOnly(); //check if it is a POST response

		$fileTransfer = new Zend_File_Transfer_Adapter_Http();
		if ($fileTransfer->isUploaded('upload_file'))
		{
			$fileInfo = $fileTransfer->getFileInfo('upload_file');
			$fileName = $fileInfo['upload_file']['tmp_name'];
		}
		else
		{
			$fileName = $this->_input->filterSingle('server_file', XenForo_Input::STRING);
		}
		
		if (!file_exists($fileName) || !is_readable($fileName))
		{
			throw new XenForo_Exception(new XenForo_Phrase('please_enter_valid_file_name_requested_file_not_read'), true);
		}
		
		$file = new SimpleXMLElement($fileName, null, true);
		
		if($file->getName() != 'bbcode')
		{
			throw new XenForo_Exception(new XenForo_Phase('bbcm_invalidXML'), true);
		}
		
		$dw = XenForo_DataWriter::create('KingK_BbCodeManager_DataWriter_CustomBbCodes');
		
		if(is_array($this->_getCustomBbCodeModel()->getCustomBbCodeById($file->tag)))
		{
			throw new XenForo_Exception(new XenForo_Phrase('bbcm_importAlreadyExists'), true);
		}
		
		$dw->bulkSet(array(
				'tag' => $file->tag,
				'title' => $file->title,
				'description' => $file->description,
				'replacementBegin' => $file->replacement->begin,
				'replacementEnd' => $file->replacement->end,
				'phpcallback_class' => $file->phpcallback->class,
				'phpcallback_method' => $file->phpcallback->method,
				'example' => $file->example,
				'active' => $file->active,
				'requiresOption' => $file->requiresOption,
				'advancedOptions' => $file->advancedOptions,
				'numberOfOptions' => $file->numberOfOptions,
				'regex' => $file->regex,
				'trimLeadingLinesAfter' => $file->trimLeadingLinesAfter,
				'plainCallback' => $file->plainCallback,
				'plainChildren' => $file->plainChildren,
				'stopSmilies' => $file->stopSmilies,
				'stopLineBreakConversion' => $file->stopLineBreakConversion,
				'hasButton' => $file->hasButton,
				'button_has_usr' => $file->button_has_usr,
				'button_usr' => $file->button_usr,
				'killCmd' => $file->killCmd,
				'custCmd' => $file->custCmd,
				'imgMethod' => $file->imgMethod,
				'buttonDesc' => $file->buttonDesc,
				'tagOptions' => $file->tagOptions,
				'tagContent' => $file->tagContent
				
			));
			
		$dw->save();

		//Update simple cache
		$this->_getCustomBbCodeModel()->SimpleCachedActiveCustomBbCodes();
		
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('custom-bb-codes')
		);
	}
	
	public function actionExport()
	{
		$tagId = $this->_input->filterSingle('tag', XenForo_Input::STRING);
		$tag = $this->_getCustomBbCodeOrError($tagId);

		$this->_routeMatch->setResponseType('xml');

		$viewParams = array(
			'bbcode' => $tag,
			'xml' => $this->_getCustomBbCodeModel()->getBbCodeXml($tag)
		);

		return $this->responseView('KingK_BbCodeManager_ViewAdmin_BbCode_Export', '', $viewParams);	
	}

	protected function _enableDisable($id)
	{
		$code = $this->_getCustomBbCodeOrError($id);
		
		//Update buttons config
		$this->_UpdateConfigsAfterEnableOrDisable($code);
		
		$dw = XenForo_DataWriter::create('KingK_BbCodeManager_DataWriter_CustomBbCodes');
		$dw->setExistingData($code['tag']);

		if($code['active'])
		{
			$dw->set('active', 0);
		}
		else
		{
			$dw->set('active', 1);
		}
		
		$dw->save();

		//Update simple cache
		$this->_getCustomBbCodeModel()->SimpleCachedActiveCustomBbCodes();
	
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('custom-bb-codes')
		);
	}

	protected function _UpdateConfigsAfterEnableOrDisable($code)
	{
		//Get all configs (rtl/ltr) to check if that button was used
		$config_all =  $this->_getCustomButtonsModel()->getAllConfig();
			
		foreach ($config_all as $config_id => $config)
		{
			//Only continue if the config was set & wasn't empty (for ie: user delete a default button before to have set a config)
			if(!isset($config['config_buttons_full']) || empty($config['config_buttons_full']))
			{
				continue;
			}
			
      			//Get back buttons full array
      			$config_buttons_full = unserialize($config['config_buttons_full']);
 
      			//Get the sub-array key (button key inside buttons config)
      			foreach ($config_buttons_full as $key => $selectedbutton)
      			{
      				if ($selectedbutton['tag'] == $code['tag'])
      				{
      					$target = $key;
      				}
      			}
     		
      			//If the button has been found let's unset if the instruction is coming from the delete function OR update if it's only an update of an existing button
      			if(isset($target))
      			{
      				if($code['active'])
      				{
					$config_buttons_full[$target]['active'] = 0;
      					$config_buttons_full[$target]['class'] = 'unactiveButton';
      				}
      				else
      				{
					$config_buttons_full[$target]['active'] = 1;
      					$config_buttons_full[$target]['class'] = 'activeButton';      				
      				}


      				//Let's serialize back the config
      				$config_buttons_full = serialize($config_buttons_full);
      			

      				//Let's write new config in the database
      				$dw = XenForo_DataWriter::create('KingK_BbCodeManager_DataWriter_CustomButtons');
      				if ($this->_getCustomButtonsModel()->getConfigById($config_id))
      				{
      					$dw->setExistingData($config_id);
      				}

      				$dw->set('config_buttons_full', $config_buttons_full);
      				$dw->save();
      
      				//Let's update the Registry
      				$this->_getCustomButtonsModel()->InsertConfigInRegistry();	
      			}
      		}		
	}

	
	protected function _getCustomBbCodeOrError($id)
	{
		$info = $this->_getCustomBbCodeModel()->getCustomBbCodeById($id);
		if (!$info)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('bbcm_bbCodeNotFound'), 404));
		}

		return $info;
	}

	protected function _getCustomBbCodeModel()
	{
		return $this->getModelFromCache('KingK_BbCodeManager_Model_CustomBbCode');
	}

	protected function _getCustomButtonsModel()
	{
		return $this->getModelFromCache('KingK_BbCodeManager_Model_CustomButtons');
	}		
}
//Zend_Debug::dump($code);