<?php
class xenCODE_AutoPing_Listener {
	public static function LoadController($class, &$extend) {
		if ($class == 'XenForo_DataWriter_Discussion_Thread') { $extend[] = $extend[] = 'xenCODE_AutoPing_DataWriter'; }
	}
}