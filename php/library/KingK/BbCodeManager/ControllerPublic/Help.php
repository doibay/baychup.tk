<?php

class KingK_BbCodeManager_ControllerPublic_Help extends XFCP_KingK_BbCodeManager_ControllerPublic_Help
{
	public function actionBbCodes()
	{
		$wrap = parent::actionBbCodes();
		$wrap->subView->params['customBbCodes'] = $this->getModelFromCache('KingK_BbCodeManager_Model_CustomBbCode')->getAllActiveCustomBbCodes('strict');
		
		return $wrap;
	}
}