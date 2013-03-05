<?php //Nulled by VxF.cc
class GFNCoders_LikeLocker_BBCode_Formatter_Base extends XFCP_GFNCoders_LikeLocker_BBCode_Formatter_Base
{
	protected $_tags = null;
	
	public function getTags()
	{
		$this->_tags = parent::getTags();
			$this->_tags['likelocker'] = array(
					'callback' => array($this, 'renderTagLikeLocker')
				);
		
		return $this->_tags;
	}
	
	public function renderTagLikeLocker(array $tag, array $rendererStates)
	{
		if ($tag['option'])
			$parts = explode(',', $tag['option']);
		
		foreach ($parts AS $part)
		{
			$partAttributes = explode(':', $part, 2);
			if (isset($partAttributes[1]))
			{
				$attrName = trim($partAttributes[0]);
				$attrValue = trim($partAttributes[1]);
				if ($attrName !== '' && $attrValue !== '')
					$attributes[$attrName] = $attrValue;
			}
		}
		if(!isset($attributes))
			$attributes['title'] = $tag['option'];
		
		if(isset($attributes['close']))
			$attributes['close'] = $attributes['close'] == 'true' ? true : false;
		
		$options = XenForo_Application::getOptions();
		
		$params['close'] = isset($attributes['close']) ? $attributes['close'] : $options->GFNLikeLocker_CloseButton;
		$params['title'] = !empty($attributes['title']) ? $attributes['title'] : $options->GFNLikeLocker_DefaultTitle;
		$params['style'] = isset($attributes['style']) ? $attributes['style'] : $options->GFNLikeLocker_Style;
		$params['timer'] = isset($attributes['timer']) ? $attributes['timer'] : $options->GFNLikeLocker_Timer;
		
		$requestPaths = XenForo_Application::get('requestPaths');
		
		$params['url'] = isset($attributes['url']) ? $attributes['url'] : $requestPaths['fullUri'];
		
		$params += array(
				'message' => $this->renderSubTree($tag['children'], $rendererStates),
				'xenOptions' => XenForo_Application::getOptions()->getOptions(),
				'randomNumber' => mt_rand()
			);
		
		if($this->_view)
			return $this->_view->createTemplateObject('GFNLikeLocker_BBCode', $params)->render();
		else
			return GFNCoders_LikeLocker_Helper_NoViewFallback::get($params);
	}
}