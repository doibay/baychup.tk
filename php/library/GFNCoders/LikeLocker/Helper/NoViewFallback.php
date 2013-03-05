<?php //Nulled by VxF.cc
class GFNCoders_LikeLocker_Helper_NoViewFallback
{
	public static function get($viewParams)
	{
		return '<script>
	jQuery(document).ready(function ($)
	{
		$(".messageText .to-lock-' . $viewParams['randomNumber'] . '").tolike({
			url: "' . $viewParams['url'] . '",
' . ($viewParams['style'] != 'default' ? '			style: "ui-locker-' . $viewParams['style'] . '"' : '') . '
			text: "' . $viewParams['title'] . '",
			locker:
			{
				timer: ' . $viewParams['timer'] . ',
				close: ' . ($viewParams['close'] ? 'true' : 'false') . '
			},
			facebook:
			{
				appId: "' . $viewParams['xenOptions']['facebookAppId'] . '"
			}
		});
	});
</script>
<div class="to-lock-' . $viewParams['randomNumber'] . '">' . $viewParams['message'] . '</div>';
	}
}