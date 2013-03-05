<?php

class KingK_BbCodeManager_ControllerAdmin_CustomButtons extends XenForo_ControllerAdmin_Abstract
{

	public function actionIndex()
	{
		$viewParams = array(
			'permissions' => XenForo_Visitor::getInstance()->hasAdminPermission('customBBCodes')
		);

		return $this->responseView('XenForo_ViewAdmin_Custom_Buttons_Homepage', 'custom_buttons_homepage', $viewParams);
	}

	public function actionEditorConfigltr()
	{
		return self::EditorConfig('ltr');
	}

	public function actionEditorConfigrtl()
	{
		return self::EditorConfig('rtl');	
	}

	public function EditorConfig($languedirection)
	{
		//Get config and all buttons
		$xen = self::XenButtons($languedirection);
		$buttons = $this->_getCustomBbCodeModel()->getBbCodesWithButton();
		$buttons = self::addButtonClass($buttons);

		$config =  $this->_getCustomButtonsModel()->getConfigByType($languedirection);

		//Look inside config which buttons are already inside the editor and take them back from the buttons list
		if(!empty($config['config_buttons_full']))
		{
			$buttons = array_merge($xen['Buttons'], $buttons);
			
			$config['config_buttons_full'] = unserialize($config['config_buttons_full']);

			foreach($config['config_buttons_full'] as $key => $selectedbutton)
			{
				if((!in_array($selectedbutton['tag'], array('separator', 'carriage'))) AND !isset($buttons[$selectedbutton['tag']]))
				{
					//If a button has been deleted from database, hide it from the the selected button list (It shoudn't happen due to actionDelete function)
					unset($config['config_buttons_full'][$key]);
				}
				else
				{
					//Hide all buttons which are already used from the available buttons list
					unset($buttons[$selectedbutton['tag']]);
				}
			}
		
			//Create a new array with the line ID as main key 
			$lines = array();
			$line_id = 1;
			foreach($config['config_buttons_full'] as $button)
			{
				if($button['tag'] == 'carriage')
				{
					$line_id++;
				}
				else
				{
					$lines[$line_id][] = $button;
				}
			}
		}
		else
		{
			//If the config is blank let's insert the default XenForo Configuration
			$lines = $xen['blankConfig'];
		}

		$viewParams = array(
			'codes' => $buttons,
			'config' => $config,
			'lines' => $lines,		
			'permissions' => XenForo_Visitor::getInstance()->hasAdminPermission('customBBCodes')
 		);
		return $this->responseView('XenForo_ViewAdmin_CustomButtons_Config', 'custom_buttons_config', $viewParams);
	}

	public function actionPostConfig()
	{
		$this->_assertPostOnly();

		// fetch and clean the message text from input
		$config_id = $this->_input->filterSingle('config_id', XenForo_Input::STRING);
		$config_type = $this->_input->filterSingle('config_type', XenForo_Input::STRING);		
		$config_buttons_order = $this->_input->filterSingle('config_buttons_order', XenForo_Input::STRING);
		$config_buttons_order = str_replace('button_', '', $config_buttons_order); // 'buttons_' prefix was only use for pretty css		

      		//Get buttons
      		$xen = self::XenButtons($config_type);	//Get XenForo buttons to add them in global buttons		
      		$buttons = $this->_getCustomBbCodeModel()->getBbCodesWithButton();
      		$buttons = self::addButtonClass($buttons);	
      		$buttons = array_merge($xen['Buttons'], $buttons);		
		
		//If user has disable javascript... prevent to register a blank config in database
		if(empty($config_buttons_order))		
		{
			$config_buttons_order = $xen['list']; //Default Xen layout
		}
		
      		//Get selected buttons from user configuration and place them in an array
      		$selected_buttons =  explode(',', $config_buttons_order);

      		//Create the final data array
      		$config_buttons_full = array();
      	
      		foreach ($selected_buttons as $selected_button)
      		{
      			if(!empty($selected_button))
      			{
      				//to prevent last 'else' can't find any index, id must be: id array = id db = id js (id being separator)
      				if($selected_button == 'separator')
      				{
      					$config_buttons_full[] = array('tag' => 'separator', 'button_code' => '|');
      				}
      				elseif($selected_button == '#')
      				{
      					$config_buttons_full[] = array('tag' => 'carriage', 'button_code' => '#');
      				}
      				else
      				{
      					if(isset($buttons[$selected_button])) //Check if the button hasn't been deleted
      					{
      						$config_buttons_full[] = $buttons[$selected_button];
      					}
      				}
      			}
      		}


		//Choose what to display in the ajax response
		$ajaxresponse =  str_replace('separator', '|', $config_buttons_order); // <= Just  for a nicer display

		//Save in Database		
		$config_buttons_full = serialize($config_buttons_full);

		$dw = XenForo_DataWriter::create('KingK_BbCodeManager_DataWriter_CustomButtons');
		if ($this->_getCustomButtonsModel()->getConfigById($config_id))
		{
			$dw->setExistingData($config_id);
		}

		$dw->set('config_buttons_order', $config_buttons_order);
		$dw->set('config_buttons_full', $config_buttons_full);

		$dw->save();
		
		//Save into Registry
		$this->_getCustomButtonsModel()->InsertConfigInRegistry();
		
		
		$displayME = 0;//Ajax Debug
		if($displayME == 1)
		{
			// Ajax response ("only run this code if the action has been loaded via XenForo.ajax()")
			if ($this->_noRedirect())
			{

				$viewParams = array(
					'ajaxresponse' => $ajaxresponse,
				);

				return $this->responseView(
					'KingK_BbCodeManager_ViewAdmin_CustomButtons',
					'custombuttons_response',
					$viewParams
				);
			}
		}
		
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('custom_buttons_config')
		);
	}

	public function XenButtons($languedirection)
	{
      		//Default configuration
      		
      		if ($languedirection != 'rtl')
      		{
      			$alignbuttons = 'justifyleft,justifycenter,justifyright';
      			$indentOutdentButtons = 'outdent,indent';
      			$undoRedoButtons = 'undo,redo';
      		}
      		else
      		{
      			$alignbuttons = 'justifyright,justifycenter,justifyleft';
      			$indentOutdentButtons = 'indent,outdent';
      			$undoRedoButtons = 'redo,undo';
      		}
      
      		$xen_grids[1] = 'removeformat,|,fontselect,fontsizeselect,forecolor,xenforo_smilies,|,' . $undoRedoButtons;
      		$xen_grids[2] = 'bold,italic,underline,strikethrough,|,'. $alignbuttons . ',|,bullist,numlist,' . $indentOutdentButtons . ',|,link,unlink,image,xenforo_media,|,xenforo_code,xenforo_custom_bbcode';
      		
      		
      		//Init final variables
		$output['list'] = $xen_grids[1] . ',#,' . $xen_grids[2];
		$output['list'] = str_replace('|', 'separator', $output['list']);
		
      		$output['Buttons'] = array();
      		$output['blankConfig'] = array();
      		
      		//Bake final variables
      		foreach($xen_grids as $key => $grid)
      		{
      			$xen_buttons =  explode(',', $grid);
      			
      			foreach($xen_buttons as $xen_code)
      			{
      				if($xen_code != '|')
      				{
      					$output['blankConfig'][$key][] = array('tag' => $xen_code, 'button_code' => $xen_code, 'class' => 'xenButton');
      					$output['Buttons'][$xen_code] = array('tag' => $xen_code, 'button_code' => $xen_code, 'class' => 'xenButton');
      				}
      				else
      				{
      					$output['blankConfig'][$key][] = array('tag' => 'separator', 'button_code' => $xen_code, 'class' => 'xenButton');					
      				}
      			}
      		}	
	
		return $output;
	}
	
	public function addButtonClass($buttons)
	{	
		foreach($buttons as &$button)
		{
			if($button['tag'][0] == '@')
			{
				$button['class'] = 'orphanButton';
			}
			else
			{
				$button['class'] = 'activeButton';			
			}

			if(!$button['active'])
			{
				$button['class'] = 'unactiveButton';
			}
		}
		
		return $buttons;
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
//Zend_Debug::dump($contents);