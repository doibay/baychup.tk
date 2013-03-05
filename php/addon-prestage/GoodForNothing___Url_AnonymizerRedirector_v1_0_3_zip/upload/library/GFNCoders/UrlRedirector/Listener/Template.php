<?php
class GFNCoders_UrlRedirector_Listener_Template
{
	public static function postRender($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template)
	{
		if($template instanceof XenForo_Template_Public)
		{
			$options = XenForo_Application::get('options');
			
			$whiteList = explode("|", $options->GFNUrlRedirector_Whitelist);
			
			$boardUrl = str_replace('://', '', strstr($options->boardUrl, '://'));
			$whiteList[] = strstr($boardUrl, '/', true) === false ? $boardUrl : strstr($boardUrl, '/', true);
			
			preg_match_all('/href="(http.*?)"/is', $content, $matches);
			preg_match('/href="(http.*?)" class="GFNSkip/i', $content, $beingRedirected);
			
			if(isset($beingRedirected[1]))
				$whiteList[] = $beingRedirected[1];
			
			foreach($matches[1] as $match)
			{
				$modify = true;
				
				foreach($whiteList as $whiteListed)
				{
					if(!empty($whiteListed))
						if(stripos($match, $whiteListed) !== false)
						{
							$modify = false;
						}
				}
				
				if($modify)
					$content = str_ireplace('href="' . $match . '"', 'href="' . XenForo_Link::buildPublicLink('redirect', null, array('url' => urlencode($match))) . '"', $content);
			}
		}
	}
}