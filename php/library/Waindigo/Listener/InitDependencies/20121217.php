<?php

abstract class Waindigo_Listener_InitDependencies
{
	/**
	 * Standard approach to caching other model objects for the lifetime of the model.
	 *
	 * @var array
	 */
	protected $_modelCache = array();

	/**
	* @var XenForo_Dependencies_Abstract
	*/
	protected static $_dependencies = null;
	protected static $_data = array();

	/**
	 * @param XenForo_Dependencies_Abstract $dependencies
	 * @param array $data
	 */
	public function __construct(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		if (is_null(self::$_dependencies)) self::$_dependencies = $dependencies;
		if (empty(self::$_data)) self::$_data = $data;
	}

	public function run()
	{
		// do nothing
	}

	protected function _run()
	{
		try {
			return $this->run();
		} catch (Exception $e) {
			// do nothing
		}
	}

	/**
	 * Gets the specified model object from the cache. If it does not exist,
	 * it will be instantiated.
	 *
	 * @param string $class Name of the class to load
	 *
	 * @return XenForo_Model
	 */
	public function getModelFromCache($class)
	{
	    if (!isset($this->_modelCache[$class])) {
	        $this->_modelCache[$class] = XenForo_Model::create($class);
	    }

	    return $this->_modelCache[$class];
	}
	
	public function addHelperCallbacks(array $helperCallbacks)
	{
	    XenForo_Template_Helper_Core::$helperCallbacks = array_merge(
	        XenForo_Template_Helper_Core::$helperCallbacks, $helperCallbacks);
	}
}