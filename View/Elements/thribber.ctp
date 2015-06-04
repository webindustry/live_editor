<script>
	var thribber = {
		types : {
			'standard' : {
				'img' : 'throbber24.gif',
				'instances' : 0
			},
			'fullscreen' : {
				'img' : 'throbber24.gif',
				'modal' : true,
				'instances' : 0
			},
			'topstrip' : {
				'img' : 'throbber24.gif',
				'htmlClass' : 'thribber_strip',
				'instances' : 0
			},
			'basestrip' : {
				'img' : 'throbber24.gif',
				'htmlClass' : 'thribber_strip',
				'instances' : 0
			},
			'small' : {
				'img' : 'throbber22.gif',
				'instances' : 0
			},
			'tiny' : {
				'img' : 'throbber16.png',
				'instances' : 0
			},
			'chrome' : {
				'img' : 'throbber_chrome_faster.png',
				'instances' : 0
			},
		},
		manager : function(type,status,msg,callback,kill){

			//callback is used when we remove a thribber
			//e.g. thribber.manager('mythribber','remove',null,{error:"message"});
			
			if(typeof status == "undefined"){status = 'add';}

			var thribject = thribber.types[type];
			var thribelem = $('#thribber_'+type);
			
			if(typeof msg == "undefined"){
				msg = '';
				if(thribject.msg != null){
					msg = '<div class="msg">'+thribject.msg+'</div>';
				}
			} else {
				msg = '<div class="msg">'+msg+'</div>';
			}

			htmlClass = '';
			if(thribject.htmlClass != null){
				htmlClass = thribject.htmlClass;
			}
			
			if(status == 'add'){
				if(thribject.instances == 0){
					if(thribelem.length != 0){
						thribelem.show();
					} else {
						$('BODY').append('<div id="thribber_'+type+'" class="thribber '+htmlClass+'"><span><img src="'+APP_BASE_URI+'img/'+thribject.img+'" />'+msg+'</span></div>');
						if(thribject.modal != null){
							$('#thribber_'+type).append('<div class="modal" />');	
						}
					}
				}
				thribject.instances++;
			}
			
			if(status == 'remove'){
				if(thribject.instances > 0){
					$('#thribber_'+type).remove();
					thribject.instances--;
				}
				if(typeof callback != "undefined"){
					if(typeof callback.error != "undefined"){
						flash.manager('error',callback.error);
					} else if(typeof callback.success != "undefined"){
						flash.manager('success',callback.success);
					}
				}
				if(typeof kill != "undefined"){
					$('.thribber.error').fadeOut();
				}
			}

			
			/*-DEBUG-*/
			if(debuggery){
				debug(9,'Throbbers',thribber.types.toSource());
			}
			/*-DEBUG-*/
				
		}
	};
	var flash = {
		types : {
			'error' : {
				'icon' : 'glyphicon-exclamation-sign',
				'htmlClass' : 'danger',
			},
			'success' : {
				'icon' : 'glyphicon-ok',
				'htmlClass' : 'success',
			},
		},
		manager : function(type,msg,fade){
			var flashes = flash.types[type];
			var alertClass = 'alert-'+flashes.htmlClass;
			
			if(typeof fade != "undefined"){
				fade = "auto";
				$('.'+alertClass).fadeOut().remove();
			} 
			var count = $('.alert').length;
			var myAlert = $('<div style="bottom:'+(count*50)+'px" class="alert '+alertClass+' alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><span class="glyphicon '+flashes.icon+'" /></span> '+msg+'</div>');
			$('BODY').append(myAlert);
			if(fade == "auto"){
				flashFade(myAlert,3000);
			} else {
				$('alert-'+flashes.htmlClass).alert();
			}
		},
		kill : function(type){
			if(typeof type != "undefined"){
				$('.alert').fadeOut().remove();
			} else {
				$('.alert-'+type).fadeOut().remove();
			}
		}
	};
	function flashFade(myAlert, delay) {
	   window.setTimeout(function() { myAlert.fadeTo(200, 0).slideUp(200, function(){ $(this).remove(); }); }, delay);
	}
</script>