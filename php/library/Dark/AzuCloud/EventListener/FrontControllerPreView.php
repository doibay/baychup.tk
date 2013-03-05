<?php

class Dark_AzuCloud_EventListener_FrontControllerPreView
{
	public static function listen(XenForo_FrontController $fc, XenForo_ControllerResponse_Abstract &$controllerResponse, XenForo_ViewRenderer_Abstract &$viewRenderer, array &$containerParams)
	{
		
		$options = XenForo_Application::get('options');
		
		if(!$options->dark_azucloud_enabled) return;
		
		$search_engines = array(
			"google." => "q",
			"search.yahoo." => "p",
			"bing." => "q",
			"ask." => "q",
			"search.aol." => "query",
			"search.msn." => "q",
			"search.live." => "q"
		);        

		/** @var Dark_AzuCloud_Model_Nakano */
		$azumodel = Xenforo_Model::create('Dark_AzuCloud_Model_Nakano');
		
		$route = $fc->getRequest()->getParam('_matchedRoutePath');        
		
		// If we're in the admin panel or on an error page bail out before the route/controller match
		if(
			(
				substr($controllerResponse->controllerName, 0, 23) != 'XenForo_ControllerAdmin' && 
				$azumodel->matchPage($controllerResponse->controllerName, $controllerResponse->controllerAction, $route)
			) && !( 
				$controllerResponse instanceof XenForo_ControllerResponse_Exception ||
				$controllerResponse instanceof XenForo_ControllerResponse_Error
			)
		){
			if(strlen($route) > 0){
				
				// First we try and add any search terms coming with the request
				if(!empty($_SERVER['HTTP_REFERER'])){
					
					$referer_parsed = parse_url($_SERVER['HTTP_REFERER']);   
					// invalid url in referer? bail!
					if($referer_parsed !== false && array_key_exists('host', $referer_parsed) && !empty($referer_parsed['host'])){							
							
						$referer = strtolower($referer_parsed['host']);
						if(substr($referer, 0, 4) == 'www.')
							$referer = substr($referer, 4);
							
						foreach($search_engines as $search_engine => $delim){
							if(substr($referer, 0, strlen($search_engine)) == $search_engine){
								
								$clean_referer = str_replace(array('&fp_ip=', 'site%3A', '&esq='), '', $_SERVER['HTTP_REFERER']);
								
								if(preg_match('/'.$delim.'=(.*?)&/', $clean_referer, $matches) && count($matches) == 2){
									
									$query = urldecode($matches[1]);
									$query = str_replace(array('"', "'"), "", $query);
									$query = implode(' ', preg_split('/[\s,\+]+/',$query));
									
									if($options->dark_azucloud_operators){
										$query = preg_replace('/(^|[\s])[-\+~](\S+)/', '$1$2', $query);
										$query = preg_replace('/(^|[\s])(?:allintitle|allinurl|allinanchor|inanchor|intext|intitle|inurl|link|site)\:(\S+)/', '$1$2', $query);
									}
																	
									// Very unlikely we want anything < 3 letters ;)
									if(strlen($query) > 2 &&
									// Or anything with the full board URL in it
									(!$options->dark_azucloud_boardurl || stripos(str_ireplace("www.", "", $query), str_ireplace("www." ,"", $options->boardUrl)) === false)
									){
										$azumodel->hitSearchTerm($query, $route);
									}
								}
							}
						}				    			        	    				
					}
				}
				
				// Next we want to get the data to stick in the tag cloud...
				$terms = $azumodel->getTermsForRoute($route, XenForo_Application::get('options')->dark_azucloud_limit, XenForo_Application::get('options')->dark_azucloud_cutoff);

				if(count($terms) > 0){
					foreach($terms as &$term){
						$term['value'] = XenForo_Helper_String::censorString($term['value']);
						$term['tag'] = 'span';
					}
					$containerParams['dark_azucloud_enable'] = true;
				}
				if(array_key_exists(0, $terms))
					$terms[0]['tag'] = 'h2';
				if(array_key_exists(1, $terms))
					$terms[1]['tag'] = 'h3';
				if(array_key_exists(2, $terms))
					$terms[2]['tag'] = 'h4';
				if(array_key_exists(3, $terms))
					$terms[3]['tag'] = 'strong';
				$containerParams['dark_azucloud_terms'] = $terms;
				$containerParams['dark_azucloud_count'] = count($terms);
				
			}
		}
		
		
		
	}
}
	