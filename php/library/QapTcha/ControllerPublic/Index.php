<?php
  
class QapTcha_ControllerPublic_Index extends XenForo_ControllerPublic_Abstract
{
	public function actionPost()
	{
		$this->_assertPostOnly();

		if(!isset($_SESSION)) session_start();

		$message = true;
		$_SESSION['iQaptcha'] = false;

		if( isset($_POST['action']) )
		{
			if( htmlentities($_POST['action'], ENT_QUOTES, 'UTF-8') == 'qaptcha' )
			{
				$_SESSION['iQaptcha'] = true;
				if( !$_SESSION['iQaptcha'] )
				{
					$message = false;
				}
			}
			else
			{
				$message = false;
			}
		}
		else
		{
			$message = false;
		}

		$this->_routeMatch->setResponseType('json');
		return $this->responseMessage($message);
	}
}