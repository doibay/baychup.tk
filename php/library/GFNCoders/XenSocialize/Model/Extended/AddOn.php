<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Model_Extended_AddOn extends XFCP_GFNCoders_XenSocialize_Model_Extended_AddOn
{
	public function checkIfAddOnExistsAndEnabled($addOnId)
	{
		return $this->_getDb()->fetchOne("SELECT title FROM xf_addon WHERE addon_id = ? AND active = 1", array($addOnId)) ? true : false;
	}
}