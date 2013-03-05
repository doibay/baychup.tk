<?php
	
class Dark_AzuCloud_Model_Nakano extends XenForo_Model
{	
	private $controllers;
	private $routes;
	
	public function getActiveControllers(){	
		
		if($this->controllers != null)
			return $this->controllers;
			
		$controlleroption = trim(XenForo_Application::get('options')->dark_azucloud_controllers);
		$controllerlist = explode("\n", str_replace("\r", "", $controlleroption));
		$controllers = array();
		
		foreach($controllerlist as &$controller){
			$controllername = trim(substr($controller, 0, strpos($controller, ":")));
			$controller = trim(substr($controller, strpos($controller, ":")+1));
			$controller = explode(",", $controller);
			foreach($controller as &$action){
				$action = ucfirst($action);
			}
			$controllers[$controllername] = $controller;
		}
		
		$this->controllers = $controllers;
		return $controllers;
	}	
	
	public function getActiveRoutes(){
		
		if($this->routes != null)
			return $this->routes;			
		
		$routes = trim(XenForo_Application::get('options')->dark_azucloud_routes);
		$routes = explode("\n", str_replace("\r", "", $routes));
		$routes_extra = array();
		foreach($routes as $key => &$route){
			$route = trim($route);
			if($route == ''){
				unset($routes[$key]);
				continue;
			} 
			
			// stick in a slash if there isn't already, otherwise remove it and keep existing either way   		
			if(substr($route, -1) == '/')
				$routes_extra[] = substr($route, 0, strlen($route)-1);
			else
				$routes_extra[] = $route.'/';    
					
		}
		$routes = array_merge($routes, $routes_extra);
			
		$this->routes = $routes;
		return $routes;		
	}
	
	public function matchPage($controller, $action, $route){
		$controllers = $this->getActiveControllers();
		$routes = $this->getActiveRoutes();
		return (array_key_exists($controller, $controllers) && in_array(strtolower($action), array_map('strtolower', $controllers[$controller]), false)) || in_array(strtolower($route), array_map('strtolower', $routes));
	}
								
	
	public function prepareTermConditions(array $conditions, array &$fetchOptions)
	{
		$db = $this->_getDb();
		$sqlConditions = array();

		if (!empty($conditions['value']))
		{
			if (is_array($conditions['value']))
			{
				$sqlConditions[] = 'terms.value LIKE ' . XenForo_Db::quoteLike($conditions['value'][0], $conditions['value'][1], $db);
			}
			else
			{
				$sqlConditions[] = 'terms.value LIKE ' . XenForo_Db::quoteLike($conditions['value'], 'lr', $db);
			}
		}           
		
		return $this->getConditionsForClause($sqlConditions);
	}

	
	public function prepareTermOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
	{
		$choices = array(
			'value' => 'lower(terms.value), hits',
			'hits' => 'hits desc, last_clicked',
			'last_clicked' => 'last_clicked desc, hits'
		);
		return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
	}
	
	
	
	public function countTerms(array $conditions)
	{
		$fetchOptions = array();
		$whereClause = $this->prepareTermConditions($conditions, $fetchOptions);                                                                             

		return $this->_getDb()->fetchOne('
			select count(*)           
			from dark_azucloud_terms_pages pages 
			left join dark_azucloud_terms terms on pages.term_id = terms.id
			where ' . $whereClause .'  and value is not null and pages.id>0 and terms.id>0'
		);
	}       
	
	
	public function getTerms(array $conditions, array $fetchOptions = array())
	{
		$whereClause = $this->prepareTermConditions($conditions, $fetchOptions);    
		$orderClause = $this->prepareTermOrderOptions($fetchOptions, 'hits desc, last_clicked desc');    
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->fetchAllKeyed($this->limitQueryResults('
				select *               
				from dark_azucloud_terms_pages pages        
				left join dark_azucloud_terms terms on pages.term_id = terms.id
				where ' . $whereClause . ' and value is not null and pages.id>0 and terms.id>0               
				'. $orderClause .'
			', $limitOptions['limit'], $limitOptions['offset']
		), 'pages.id');
	}
	
	public function getTermById($id){
		return $this->_getDb()->fetchRow('		
			select * from dark_azucloud_terms where id = ?
		', $id);				
	}
	
	public function hitSearchTerm($term, $route)
	{
		// todo: reduce number of queries, though they are pretty light as is
		$this->_getDb()->query('		
			insert ignore into dark_azucloud_terms
			set value = ?, block=0
		', array($term));
		
		$term_id = $this->_getDb()->fetchOne('		
			select id from dark_azucloud_terms 
			where value = ?
		', array($term));
		
		$this->_getDb()->query('		
			insert ignore into dark_azucloud_terms_pages
			set term_id = ?, route = ?, hits=0
		', array($term_id, $route));
		
		$this->_getDb()->query('		
			update dark_azucloud_terms_pages 
			set hits = hits + 1, last_clicked=UNIX_TIMESTAMP()
			where term_id = ? and route = ?
		', array($term_id, $route));		
	}
	
	public function getTermsForRoute($route, $limit, $days){
		return $this->_getDb()->fetchAll('		
			select value, hits 
			from dark_azucloud_terms_pages pages 
			left join dark_azucloud_terms terms on pages.term_id = terms.id 
			where route = ? and terms.block = 0 and terms.value is not null and last_clicked > ? 
			order by hits desc, last_clicked desc
			limit ? 
		', array($route, time()-$days*60*60*24, $limit));	
	}
	
	
}