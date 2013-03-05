<?php

class PageEditor_BbCode_Formatter_ToSave extends XenForo_BbCode_Formatter_Base
{
	public function renderTagAttach(array $tag, array $rendererStates)
	{
		$id = intval($this->stringifyTree($tag['children']));
		if (!$id)
		{
			return '';
		}

		return sprintf('[ATTACH%2$s]%1$d[/ATTACH]', $id, (strtolower($tag['option']) == 'full' ? '=full' : ''));
	}
}