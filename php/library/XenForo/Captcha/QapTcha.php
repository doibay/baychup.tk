<?php

class XenForo_Captcha_QapTcha extends XenForo_Captcha_Abstract
{
	public function __construct(array $config = null) { }

	public function isValid(array $input)
	{
		if( !isset($_SESSION) ) session_start();

		if( isset($_POST['iQapTcha']) && empty($_POST['iQapTcha']) && isset($_SESSION['iQaptcha']) && $_SESSION['iQaptcha'] )
		{
			unset($_SESSION['iQaptcha']);
			return true;
		}

		return false;
	}

	public function renderInternal(XenForo_View $view)
	{
		return $view->createTemplateObject('captcha_qaptcha', array(
			'noscript_code_notice' => new XenForo_Phrase('qaptcha_noscript_code_notice')
		));
	}
}