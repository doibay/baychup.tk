<?php

class KingK_BbCodeManager_BbCode_Formatter_Default
{
	public static function parseTagFlash(array $tag, array $rendererStates, &$parentClass)
	{
		if (!empty($tag['option']) && $parentClass->parseMultipleOptions($tag['option']))
		{
			$attributes = $parentClass->parseMultipleOptions($tag['option']);
			$attributes['width'] = $attributes[0];
			$attributes['height'] = $attributes[1];
			
			$src = $tag['children'][0];
		}
		else
		{
			$attributes['width'] = '550';
			$attributes['height'] = '400';
			$src = $tag['children'][0];
		}
		return '<object width="' . $attributes['width'] . '" height="' . $attributes['height'] . '">
						<param name="movie" value="' . $src . '">
							<embed src="' . $src . '" width="' . $attributes['width'] . '" height="' . $attributes['height'] . '">
							</embed>
					</object>';
	}
	
	public static function parseTagUser(array $tag, array $rendererStates, &$parentClass)
	{
		$userModel = XenForo_Model::create('XenForo_Model_User');
		
		if ($tag['option'] != NULL)
		{
			$userid = $tag['option'];
		}
		else
		{
			$userid = XenForo_Model_User::getUserIdFromUser($userModel->getUserByName($tag['children'][0]));
		}
		$url = XenForo_Link::buildPublicLink('members', array('user_id' => $userid, 'username' => $tag['children'][0]));

		list($class, $target) = XenForo_Helper_String::getLinkClassTarget($url);
		$class = $class ? " class=\"$class\"" : '';
		$target = $target ? " target=\"$target\"" : '';
		$noFollow = (empty($rendererStates['noFollowDefault']) ? '' : ' rel="nofollow"');

		return '<a href="' . htmlspecialchars($url) . '"' . $target . $class . $noFollow . '>' . $tag['children'][0] . '</a>';
	}
		
	public static function parseTagSpoiler(array $tag, array $rendererStates, &$parentClass)
	{
		if($tag['option'] != NULL)
		{
			if(is_array($parentClass->parseMultipleOptions($tag['option'])) && count($parentClass->parseMultipleOptions($tag['option'])) > 1)
			{
				$attributes = $parentClass->parseMultipleOptions($tag['option']);
				$buttonText = htmlspecialchars($attributes[0]);
				$hideText = htmlspecialchars($attributes[1]);
				$noScriptHelp = '';
			}
			else
			{
				$tag['option'] = htmlspecialchars($tag['option']);
				$buttonText = $tag['option'] . new XenForo_Phrase('bbcm_bbcode_spoiler_open');
				$hideText = $tag['option'] . new XenForo_Phrase('bbcm_bbcode_spoiler_close');
				$noScriptHelp = '<noscript><span class="bbcm_spoil_noscript_hastitle">' . $tag['option'] . '</span> <span class="bbcm_spoil_noscript_desc">' .  
				new XenForo_Phrase('bbcm_bbcode_spoiler_noscript_desc_generic') . '</span></noscript>';
			}
		}
		else
		{
			$buttonText = new XenForo_Phrase('bbcm_bbcode_spoiler_showspoiler');
			$hideText = new XenForo_Phrase('bbcm_bbcode_spoiler_hidespoiler');
			$noScriptHelp = '<noscript><span class="bbcm_spoil_noscript_hastitle">' . new XenForo_Phrase('bbcm_bbcode_spoiler_title') . '</span> <span class="bbcm_spoil_noscript_desc">' .
			new XenForo_Phrase('bbcm_bbcode_spoiler_noscript_desc') . '</span></noscript>';
		}
	
		$content = $parentClass->renderSubTree($tag['children'], $rendererStates);
		
		$output = '<div class="bbCodeBlock bbCodeQuote bbcmSpoilerBlock">
						<div class="attribution type">' . $noScriptHelp . '			
							<span class="button JsOnly">
								<span class="bbcm_spoiler_show">' . $buttonText . '</span>
								<span class="bbcm_spoiler_hide" style="display:none">' . $hideText . '</span>
							</span>
						</div>
						<div class="quotecontent">
							<div class="bbcm_spoiler_noscript"><blockquote>' . $content . '</blockquote></div>
						</div>
					</div>';
		return $output;
	}
}

?>