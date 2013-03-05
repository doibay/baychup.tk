/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	XenForo.LikeLinkHide = function($link)
	{
		$link.click(function(e)
		{
			e.preventDefault();
	
			var $link = $(this);
	
			XenForo.ajax(this.href, {}, function(ajaxData, textStatus)
			{
				if (XenForo.hasResponseError(ajaxData))
				{
					return false;
				}
				
				// received a redirect rather than a view - follow it.
				if (ajaxData._redirectStatus && ajaxData._redirectTarget)
				{
					window.location.href = ajaxData._redirectTarget;
					location.reload();
					return;
				}
	
				$link.stop(true, true);
	
				if (ajaxData.term) // term = Like / Unlike
				{
					$link.find('.LikeLabel').html(ajaxData.term);
	
					if (ajaxData.cssClasses)
					{
						$.each(ajaxData.cssClasses, function(className, action)
						{
							$link[action == '+' ? 'addClass' : 'removeClass'](className);
						});
					}
				}
	
				if (ajaxData.templateHtml === '')
				{
					$($link.data('container')).xfFadeUp(XenForo.speed.fast, function()
					{
						$(this).empty().xfFadeDown(0);
					});
				}
				else
				{
					var $container    = $($link.data('container')),
						$likeText     = $container.find('.LikeText'),
						$templateHtml = $(ajaxData.templateHtml);
	
					if ($likeText.length)
					{
						// we already have the likes_summary template in place, so just replace the text
						$likeText.xfFadeOut(50, function()
						{
							var textContainer = this.parentNode;
	
							$(this).remove();
	
							$templateHtml.find('.LikeText').xfInsert('appendTo', textContainer, 'xfFadeIn', 50);
						});
					}
					else
					{
						new XenForo.ExtLoader(ajaxData, function()
						{
							$templateHtml.xfInsert('appendTo', $container);
						});
					}
				}
			});
		});
	};

	// Handle like/unlike links
	XenForo.register('a.LikeLinkHide', 'XenForo.LikeLinkHide');
}
(jQuery, this, document);