<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_DataWriter_Extended_User extends XFCP_GFNCoders_XenSocialize_DataWriter_Extended_User
{
	protected function _getFields()
	{
		return parent::_getFields() + array(
				'gfn_xensocialize_user_option' => array(
						'user_id' => array('type' => self::TYPE_UINT, 'default' => array('xf_user', 'user_id'), 'required' => true),
						'post_on_network' => array('type' => self::TYPE_BOOLEAN, 'default' => 1),
						'post_on_network_on_reply' => array('type' => self::TYPE_BOOLEAN, 'default' => 1)
					)
			);
	}
	
	protected function _preSave()
	{
		if(GFNCoders_Helper_Cache::get('GFNXenSocialize_CalledAction') == 'PreferencesSave')
		{
			$input = new XenForo_Input($_REQUEST);
			$this->set('post_on_network', $input->filterSingle('post_on_network', XenForo_Input::UINT), 'gfn_xensocialize_user_option');
			$this->set('post_on_network_on_reply', $input->filterSingle('post_on_network_on_reply', XenForo_Input::UINT), 'gfn_xensocialize_user_option');
		}
		
		return parent::_preSave();
	}
}