<?php

class KingK_BbCodeManager_EventListener_Templates_InitEditorGrid
{
	public static function Override($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
	   switch ($hookName) {
		case 'editor_tinymce_init':
			$options = XenForo_Application::get('options');
			
			if(!empty($options->bbcm_debug_tinymcehookdisable))
			{
				break;
			}
			
		       	$visitor = XenForo_Visitor::getInstance();

		        //Get text direction
			$LtrRtl = 'ltr';
			if($template->getParam('pageIsRtl') === true)
			{
		        	$LtrRtl = 'rtl';
		        }

			//Get buttons config and check if can or can't proceed
			$myConfig = XenForo_Model::create('XenForo_Model_DataRegistry')->get('kingk_bbcm_buttons');
			
			if(empty($myConfig))
			{
				break; //Blank config (after addon install) => stop the process
			}
			elseif(empty($myConfig['kingk_bbcm_buttons'][$LtrRtl]['config_buttons_order']))
			{
				break; //This language direction doesn't have any configuration => stop the process
			}

			
			//Get the Grid & Custom buttons commands (Ready to use=> can't do this in admin options because need to check user permissions here)
			$setup = self::bakeGridCmd($myConfig['kingk_bbcm_buttons'][$LtrRtl]['config_buttons_full'], $options, $visitor);

			//Let's do some clean up: all previous modifications done by other addons will be erased (providing these addons were executed after this listener function which execution order is 5)
			$contents = preg_replace("#theme_xenforo_buttons[\s\S]+?',#i", '', $contents);
			$contents = preg_replace("#setup(?:\s)?:[\s\S]+?},#i", '', $contents);			
			
			//Insert the grid & setup
			$contents = preg_replace('#xenforo_smilies:#', $setup['cmd'] . $setup['grid'] . '$0', $contents);

			if(!empty($options->bbcm_debug_tinymcehookcontent) && $visitor['is_admin'])
			{
				Zend_Debug::dump($contents);
			}
			
			break;
		}
		
	}

	public static function bakeGridCmd($buttons, $options, $visitor)
	{
		$allButtons = unserialize($buttons);

		//Server info for buttons icons
		$server_root = preg_replace('#/\w+?\.\w{3,4}$#', '', $_SERVER["SCRIPT_FILENAME"]);
		$icons_folder = $server_root . '/styles/KingK/editor/';

		//Visitor info
		$visitorUserGroupIds = array_merge(array((string)$visitor['user_group_id']), (explode(',', $visitor['secondary_group_ids'])));

		//Only to make debug display cleaner		
		$br = "\r\n\t\t\t\t";

		//Init Grid
		$output['grid'] = "theme_xenforo_buttons1 : '";
		$line = 1;
		
		//Init Cmd
		$output['cmd'] = '';

		//Let's start the big loop
		foreach($allButtons as $button)
		{
			//Check if button active (only for Custom buttons => they will be the only ones to have the key [active]
			if(isset($button['active']) && empty($button['active']))
			{
				continue; //Next loop !
			}
			
			//Check Permissions
			if($button['button_has_usr'])
			{
				$usrOK = unserialize($button['button_usr']);

				if(!array_intersect($visitorUserGroupIds, $usrOK))
				{
					continue; //Next loop !
				}
			}

			/*****
			*	GRID CREATION
			***/

				if($button['tag'] == 'carriage')
				{
					$line++;
					$output['grid'] = substr($output['grid'], 0, -1); //must delete the last ',' from the button, then start a new line
					$output['grid'] .= "',".$br."theme_xenforo_buttons$line : '";
				}
				else
				{
					if($button['tag'] == 'separator')
					{
						$tempTag = '|';
					}
					elseif( $button['class'] != 'xenButton' && empty($button['killCmd']) ) 
					{
						//Custom buttons (classic)
						$tempTag = 'bbcm_' . $button['tag'];
					}
					elseif( $button['class'] != 'xenButton' && !empty($button['killCmd']) && !empty($button['custCmd'])  )
					{
						//Custom buttons with TinyMCE plugin
						$tempTag = $button['custCmd'];
					}
					else
					{
						//Xen buttons
						$tempTag =  $button['tag'];
					}

					$output['grid'] .= $tempTag . ',';
				}


			/*****
			*	COMMAND CREATION
			***/

				//Target custom buttons which have command options activated
				if(!$button['hasButton'] || $button['killCmd']) //will include carriage, separator & xen buttons.
				{
					continue;	
				}
	
				//Icon Management
				if($button['imgMethod'] == 'Direct')
				{
	      				if (file_exists($icons_folder . $button['tag'] . '.png'))
	      				{
	      					$icon_url = $options->boardUrl . '/styles/KingK/editor/' . $button['tag'] . '.png';
	      				}
	      				elseif (file_exists($icons_folder . $button['tag'] . '.gif'))
	      				{
	      					$icon_url = $options->boardUrl . '/styles/KingK/editor/' . $button['tag'] . '.gif';
	      				}
	      				elseif (file_exists($icons_folder . 'default.png'))
	      				{
	      					$icon_url = $options->boardUrl . '/styles/KingK/editor/default.png';
	      				}
	      				else
	      				{
	      					$icon_url = $options->boardUrl . '/styles/KingK/editor/' . $button['tag'] . '.png';
	      				}			
				}
	
	      			//Button Title Management
	      			$phrase = '';
	      			if(!empty($button['buttonDesc']))
	      			{
	      				$phrase = self::DetectPhrases($button['buttonDesc']);
	      
	      			}
	
	      			//Opening Tag Management
	      			if(empty($button['tagOptions']))
	      			{
	      				$opening = $button['tag'];
	      			}
	      			else
	      			{
	      				$opening_option = self::DetectPhrases($button['tagOptions']);
	      				$opening = $button['tag'] . '=' . $opening_option;
	      			}
	
	      			//Content Management
	      			if(empty($button['tagContent']))
	      			{
	      				$content = "ed.selection.getContent()";
	      			}
	      			else
	      			{
	      				$content_replace = self::DetectPhrases($button['tagContent']);
	      				$content = "'$content_replace'";
	      			}			
	      	
	      			//Button and Command Management
	      			$ext = $button['tag'];
	      			
				if($button['imgMethod'] == 'Direct')
				{
		      			$output['cmd'] .= "
			      			ed.addCommand('Bbcm_$ext', function() {
			      				ed.focus();
			      				ed.selection.setContent('[$opening]' + $content + '[/$ext]');
			              		});
			      	        	ed.addButton('bbcm_$ext', {
			              	        	        title : '$phrase',
			      	        	        	cmd : 'Bbcm_$ext',
			                      	        	image : '$icon_url'
			      	                });
		      		        ";
		      		}
		      		else
		      		{
		  	      		$output['cmd'] .= "
			      			ed.addCommand('Bbcm_$ext', function() {
			      				ed.focus();
			      				ed.selection.setContent('[$opening]' + $content + '[/$ext]');
			              		});
			      	        	ed.addButton('bbcm_$ext', {
			              	        	        title : '$phrase',
			      	        	        	cmd : 'Bbcm_$ext'
			      	                });
		      		        ";
		      		}
		}

		//Finish Grid creation
		$output['grid'] = substr($output['grid'], 0, -1); 
		$output['grid'] .= "',$br $br";
		
		//Correct Grid if line is empty (for example if a line has only private buttons then the users who can't access it will have an error)
		$output['grid'] = preg_replace("#theme_xenforo_buttons\d{1,2}\s*?:\s*?'(?:(?:\|(?:,)?)+?')?,#i", '', $output['grid']); //empty seperators will also be deleted

		//Finish Command creation		
		if(!empty($output['cmd']))
		{
			$output['cmd'] = "setup : function(ed) { $br" . $output['cmd'] . "$br},$br $br";
		}

		return $output;
	}

	public static function DetectPhrases($string)
	{
		if(preg_match_all('#{phrase:(.+?)}#i', $string, $captures, PREG_SET_ORDER))
		{
			foreach($captures as $capture)
			{
				$phrase = new XenForo_Phrase($capture[1]);
				$string = str_replace($capture[0], $phrase, $string);
			}
		}

		return addslashes($string);
	}

	/****

	*	Auto add template 'help_custom_bbcodes' to template 'help_bb_codes'
	*
	***/

	public static function help_custom_bbcodes($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
	        if ($hookName == 'help_bb_codes')
	        {
			$contents .= $template->create('help_custom_bbcodes', $template->getParams());
	        }
	}

	public static function bbcmspoiler($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
	        if ($hookName == 'page_container_head')
	        {
			$contents .= $template->create('bbcm_js', $template->getParams());
	        }	
	}


}
//	Zend_Debug::dump($abc);