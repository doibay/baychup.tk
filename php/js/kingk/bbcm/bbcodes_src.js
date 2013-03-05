!function($, window, document, _undefined)
{    
	XenForo.BbcmSpoiler = function($element)
	{
		//JS detected - init (inline-block tested on IE7-8)
		$element.find('.bbcm_spoiler_noscript').addClass('bbcm_spoiler').removeClass('bbcm_spoiler_noscript');
		$element.find('.button').css('display','inline-block');

		//Toggle function
		$element.find('.button').toggle(
			function () {
				$(this).parents('.bbcmSpoilerBlock').find('.bbcm_spoiler').show();
				$(this).children('.bbcm_spoiler_show').hide();
				$(this).children('.bbcm_spoiler_hide').show();
				
			},
			function () {
				$(this).parents('.bbcmSpoilerBlock').find('.bbcm_spoiler').hide();
				$(this).children('.bbcm_spoiler_show').show();
				$(this).children('.bbcm_spoiler_hide').hide();
			}
		);		
	}

	 XenForo.register('.bbcmSpoilerBlock','XenForo.BbcmSpoiler')
}
(jQuery, this, document);