!function($, window, document, _undefined)
{
	XenForo.QaptchaAction = function($form)
	{
		$form.bind('AutoValidationError', function(e)
		{
			refreshQapTcha(); 
		});
	}
	
	// *********************************************************************
	
	XenForo.register('form.AutoValidator', 'XenForo.QaptchaAction');
}
(jQuery, this, document);

jQuery.QapTcha = {
	build : function(options)
	{
	        var defaults = {
				txtLock : 'Locked : form can\'t be submited',
				txtUnlock : 'Unlocked : form can be submited',
				disabledSubmit : true,
				autoRevert : true,
				PHPfile : 'index.php?qaptcha/post'
	        };   
		
		if(this.length>0)
			return jQuery(this).each(function(i) {
				/** Vars **/
				var 
					opts = jQuery.extend(defaults, options),      
					$this = jQuery(this),
					form = jQuery('form').has($this),
					Clr = jQuery('<div>',{'class':'clr'}),
					bgSlider = jQuery('<div>',{id:'bgSlider'}),
					Slider = jQuery('<div>',{id:'Slider'}),
					Icons = jQuery('<div>',{id:'Icons'}),
					TxtStatus = jQuery('<div>',{id:'TxtStatus','class':'dropError',text:opts.txtLock}),
					inputQapTcha = jQuery('<input>',{name:'iQapTcha',value:generatePass(),type:'hidden'});
				
				/** Disabled submit button **/
				if(opts.disabledSubmit) form.find('input[type=\'submit\']').attr('disabled','disabled');
				
				/** Construct DOM **/
				$this.html(bgSlider);
				Icons.insertAfter(bgSlider);
				Clr.insertAfter(Icons);
				TxtStatus.insertAfter(Clr);
				inputQapTcha.appendTo($this);
				Slider.appendTo(bgSlider);
				$this.show();

				Slider.draggable({ 
					revert: function(){
						if(opts.autoRevert)
						{
							if(parseInt(Slider.css("left")) > 150) return false;
							else return true;
						}
					},
					containment: bgSlider,
					axis:'x',
					start: function(event,ui) {
						ui.originalPosition.top = 0;
						ui.originalPosition.left = 0;
					},
					stop: function(event,ui) {
						if(ui.position.left > 150)
						{
							// set the SESSION iQaptcha in PHP file
							jQuery.post(opts.PHPfile,{
								action : 'qaptcha'
							},
							function(data) {
								if(data.message)
								{
									Slider.draggable('disable').css('cursor','default');
									inputQapTcha.val("");
									TxtStatus.text(opts.txtUnlock).addClass('dropSuccess').removeClass('dropError');
									Icons.css('background-position', '-16px 0');
									form.find('input[type=\'submit\']').removeAttr('disabled');
								}
							},'json');
						}
					}
				});
				
				function generatePass() {
				        var chars = 'azertyupqsdfghjkmwxcvbn23456789AZERTYUPQSDFGHJKMWXCVBN';
				        var pass = '';
				        for(i=0;i<10;i++)
					{
						var wpos = Math.round(Math.random()*chars.length);
						pass += chars.substring(wpos,wpos+1);
			        	}
			        	return pass;
				}				
			});
	}
}; jQuery.fn.QapTcha = jQuery.QapTcha.build;