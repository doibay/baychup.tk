<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_ControllerPublic_Extended_Account extends XFCP_GFNCoders_XenSocialize_ControllerPublic_Extended_Account
{
	public function actionPreferencesSave()
	{
		GFNCoders_Helper_Cache::set('GFNXenSocialize_CalledAction', 'PreferencesSave');
		
		return parent::actionPreferencesSave();
	}
	
	public function actionFacebook()
	{
		$return = parent::actionFacebook();
		
		if($this->isConfirmedPost())
		{
			$disassociate = $this->_input->filter(array(
					'disassociate' => XenForo_Input::STRING,
					'disassociate_confirm' => XenForo_Input::STRING
				));
			if($disassociate['disassociate'] && $disassociate['disassociate_confirm'])
				$this->_getUserModel()->updateXenSocializeData('', 'facebook');
		}
		
		return $return;
	}
}