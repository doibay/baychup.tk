<?php
class GFNCoders_UrlRedirector_ControllerPublic_Redirect extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$options = XenForo_Application::get('options');
		$url = urldecode($this->_input->filterSingle('url', XenForo_Input::STRING));
		
		if(!$url || empty($url))
			$noUrl = true;
		else
			$noUrl = false;
		
		if($noUrl)
			$this->_response->setHttpResponseCode(404);
		else
			$this->_response->setHttpResponseCode(302);
		
		$containsDelay = 0;
		$message = $options->GFNUrlRedirector_EnableAutoRedirect ? $options->GFNUrlRedirector_AutoRedirect_Message : $options->GFNUrlRedirector_Message;
		
		$message = str_replace('{board}', "<b>$options->boardTitle</b>", $message);
		$message = str_replace('{boardUrl}', "<b>$options->boardUrl</b>", $message);
		$message = str_replace('{url}', "<a href=\"$url\" class=\"GFNSkip\" rel=\"nofollow\">$url</a>", $message);
		if($options->GFNUrlRedirector_EnableAutoRedirect)
			$message = str_replace('{delay}', "<span id=\"delay\">$options->GFNUrlRedirector_AutoRedirect_Delay</span>", $message, $containsDelay);
		
		$viewParams = array(
				'url' => $url,
				'noUrl' => $noUrl,
				'referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $options->boardUrl,
				'title' => $options->GFNUrlRedirector_EnableAutoRedirect ? $options->GFNUrlRedirector_AutoRedirect_Title : $options->GFNUrlRedirector_Title,
				'message' => $message,
				'containsDelay' => $containsDelay
			);
		
		return $this->responseView('GFNCoders_UrlRedirector_ViewPublic_Redirect', 'GFNUrlRedirector_RedirectPage', $viewParams);
	}
	public static function getSessionActivityDetailsForList(array $activities)
	{
		$output = array();
		foreach($activities as $key => $activity)
			$output[$key] = new XenForo_Phrase(XenForo_Application::get('options')->GFNUrlRedirector_EnableAutoRedirect ? 'GFNUrlRedirector_visiting_outgoing_page' : 'GFNUrlRedirector_viewing_warning_message');
		
		return $output;
	}
}