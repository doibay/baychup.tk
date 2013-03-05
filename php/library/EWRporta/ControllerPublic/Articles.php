<?php

class EWRporta_ControllerPublic_Articles extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$category = $this->_input->filterSingle('category_slug', XenForo_Input::STRING);
		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));

		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('articles', $category, array('page' => $page)));

		$options = XenForo_Application::get('options');

		if ($options->EWRporta_stylechoice['force'] && $options->EWRporta_stylechoice['style'])
		{
			$this->setViewStateChange('styleId', $options->EWRporta_stylechoice['style']);
		}

		$viewParams = array(
			'isPortal' => true,
			'layout1' => 'articles-'.$category,
			'layout2' => 'articles',
			'layout3' => 'portal',
			'category' => $category,
			'page' => max(1, $page),
		);

		return $this->responseView('EWRporta_ViewPublic_Portal', 'EWRporta_Portal', $viewParams);
	}

	public static function getSessionActivityDetailsForList(array $activities)
	{
        $output = array();

        foreach ($activities as $key => $activity)
		{
			$output[$key] = array(
				new XenForo_Phrase('viewing_portal'),
				new XenForo_Phrase('index'),
				XenForo_Link::buildPublicLink('portal'),
				false
			);
        }

        return $output;
	}
}