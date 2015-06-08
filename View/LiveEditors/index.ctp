


	<?php //jquery colorPicker
	echo $this->Html->script('colorPicker/colors.js')."\n";
	echo $this->Html->script('colorPicker/colorPicker.data.js')."\n";
	echo $this->Html->script('colorPicker/colorPicker.js')."\n";
	echo $this->Html->script('colorPicker/jQuery_implementation/jqColor.js')."\n";
	?>

<script>
var appData = {}
appData.jobs = <?php echo json_encode($jobs)?>;
appData.mode = <?php echo $mode;?>; //1: normal 2:popup
appData.ifrLocation = window;
if(appData.mode == 2){
	appData.ifrLocation = window.opener;
}
$('BODY').addClass('mode'+appData.mode);

</script>

 
<?php //app custom styles
if($mode == 2){
	echo $this->Html->css('child_window');
}
?>







<div id="navJobsWrap" style="display:none">
	<ul id="navJobs" class="myNav">
		<li><?php echo $this->Html->link('Job manager',array('controller' => 'jobs', 'action' => 'index'));?></li>
		<?php
		foreach($jobs as $k => $v):
		?>
			<li data-job='<?php echo $v['id']?>' id="navJob<?php echo $v['id']?>">
				<a href="#" class="navJob"><?php echo $v['name']?></a>
				<a href="#" class="navJobClose navJobCtrl" title="Close job">
					<span class='glyphicon glyphicon-remove-sign'></span>
				</a>
				<!-- <a href="#" class="navJobCached navJobCtrl" title="Toggle cache mode">
					<span class='glyphicon glyphicon-camera'></span>
				</a> -->
			</li>
		<?php
		endforeach;
		?>
	</ul>
</div>


<div id="loadingBlock" style="display:none"></div>
<div id="editor"></div>


<script>



/*	NOTES
 *	Globals handled by uiStateManger
 *	Job specific vars handled by jobStateManager
 */

/*

	NICE TO HAVES...
	-- ability to customise device simulation sizes
	-- integrate my em/px comversion tables
	-- codemirror to be affected by edgebar base (only needed when the time comes)
	-- codemirror needs to obey the height/width rules for edgeBars but this should be optional (ie you might want cm to be before or after the margin)
	-- when a job is loaded, compare the saved file with the live one and issue a warning if theyre different (code commpare here!!)
	-- eyedropper style color grabber
	-- multi same document codemirror in tabs
	-- ftp upload code comparison

*/

/*
//YAHOO PUBLIC PROXY
var MY_URL = 'http://bbc.co.uk';
$.getJSON("http://query.yahooapis.com/v1/public/yql?"+
            "q=select%20*%20from%20html%20where%20url%3D%22"+
            encodeURIComponent(MY_URL)+
            "%22&format=xml'&callback=?",
    function(data){
      if(data.results[0]){
        alert(data.toSource());
      } else {
        alert('NOPE!');
      }
    }
  );*/

/////////////////////////////////////////////////////

	/*-DEBUG-B-*/
	if(debuggery){
		var debugTabs = '<div id="debug">';
		debugTabs += '<ul class="tabNav">';
		debugTabs += '<li class="tab0"><a href="#debug-0"><span class="db0">Jobs</span></a></li>';
		debugTabs += '<li class="tab1"><a href="#debug-1"><span class="db1">Job Status</span></a></li>';
		debugTabs += '<li class="tab2"><a href="#debug-2"><span class="db2">Job states</span></a></li>';
		debugTabs += '<li class="tab3"><a href="#debug-3"><span class="db3">Iframe HTML</span></a></li>';
		debugTabs += '<li class="tab4"><a href="#debug-4"><span class="db4">Stylesheet status</span></a></li>';
		debugTabs += '<li class="tab5"><a href="#debug-5"><span class="db5">HTTP Response</span></a></li>';
		debugTabs += '<li class="tab6"><a href="#debug-6"><span class="db6">CodeMirror</span></a></li>';
		debugTabs += '<li class="tab7"><a href="#debug-7"><span class="db7">Input</span></a></li>';
		debugTabs += '<li class="tab8"><a href="#debug-8"><span class="db8">UI global state</span></a></li>';
		debugTabs += '<li class="tab9"><a href="#debug-9"><span class="db9">Misc.</span></a></li>';
		debugTabs += '</ul>';
		debugTabs += '<div id="debug-0"></div>';
		debugTabs += '<div id="debug-1"></div>';
		debugTabs += '<div id="debug-2"></div>';
		debugTabs += '<div id="debug-3"></div>';
		debugTabs += '<div id="debug-4"></div>';
		debugTabs += '<div id="debug-5"></div>';
		debugTabs += '<div id="debug-6"></div>';
		debugTabs += '<div id="debug-7"></div>';
		debugTabs += '<div id="debug-8"></div>';
		debugTabs += '<div id="debug-9"></div>';
		debugTabs += '</div>';
		$('BODY').prepend(debugTabs);
	}
	/*-DEBUG-E-*/

	$.ajaxSetup({
		async: false,
		timeout: 1000
	});
	preProcessTimer = 0;

	/*var CssProcessor = {};
	CssProcessor.editStatus = 0;
	CssProcessor.parseStatus = 0;*/
	
	var AppSettings = AppSettings || {};

	AppSettings.common = {};
	AppSettings.common.uiDims = {
		widths : {
		},
		heights : {
			uiBaseline : 24,
			topBar : 0, //if we have a top navigation bar, this is the height of it
			//valuesPalette : 300,
			//panelLeft : window.innerHeight - this.uiBaseline
		},
		workArea : {
			marginT : 0,
			marginR : 0,
			marginB : 0,
			marginL : 24
		}
	}

	var LiveEditor = LiveEditor || {};
	LiveEditor.common = {
		paths : {
			proxyUrlPrefix : PROXY_URL_PREFIX,
			dataStateManager : APP_BASE_URI+'live_editors/data_state_manager',
			cssProgressManager : APP_BASE_URI+'live_editors/css_progress_manager',
			cssBackupManager : APP_BASE_URI+'live_editors/css_backup_manager',
			httpResponse : APP_BASE_URI+'live_editors/check_uri',
			cachedSiteDir : APP_BASE_URI+'webroot/cached_sites',
			buildIndexPageGetter : APP_BASE_URI+'jobs/get_build_index_page',
			scrapeJobStyleData : APP_BASE_URI+'live_editors/scrape_job_style_data',
			scssProcessor : APP_BASE_URI+'scss.processor.php',
			lessProcessor : APP_BASE_URI+'less.processor.php',
		},
		cssModes : {
			1 : 'Raw CSS',
			2 : 'SCSS',
			3 : 'LESS',
		},
		preProcessRealtime : true,
		preProcessDelay : 1, //1700 //int milliseconds
		uiState : {
			data : {
				uiGlobalCss : {
					'.CodeMirror' : {
						'font-size' : '11px',
						'line-height' : '15px',
					}
				},
				cmDefaultTheme : 0,
			}
		},
		uiBackgroundFills : {
			0 : 'url('+APP_BASE_URI+'img/trans_checker.png)',
			1 : 'url('+APP_BASE_URI+'img/noise1.jpg)',
			2 : 'url('+APP_BASE_URI+'img/tex_burlap_inv.jpg)',
			3 : 'url('+APP_BASE_URI+'img/noise2.jpg)',
			4 : 'url('+APP_BASE_URI+'img/tex_burlap.jpg)',
		},
		uiBackgroundFillDefault : 4,
		inspector : {
			outlineColor : '#FF69B4',
			permittedTags : ["a","span","li","ul","em","strong","h1","h2","h3","h4","h5","h6","legend","fieldset","label","input"],
			deniedTags : ["script","noscript","link"],
			showTags : 1,
			postTranferCursorPos : 'after' //cursor behaviour in codemirror: before,after,around
		},
		iframeClickRef : 'click.ifrLive',
		iframeModifiedContentTag : "live_editor_ref",
		iframeLiveResize : false, //setting to true can be slow
		iframeTopOffset : AppSettings.common.uiDims.heights.uiBaseline, //for screenwidths smaller than LG, an optional space can be left between the top of the page and the top of the iframe
		iframeBottomOffset : AppSettings.common.uiDims.heights.uiBaseline, //for screenwidths smaller than LG, an optional space can be left between the bottom of the page and the bottom of the iframe
		layoutModes : {
			//x mode
			0 : {
				cmDefaultW : window.innerWidth - AppSettings.common.uiDims.heights.uiBaseline + 'px',
				cmDefaultH : '300px',
			},
			//y mode
			1 : {
				cmDefaultW : ( window.innerWidth / 3 ) - AppSettings.common.uiDims.heights.uiBaseline + 'px',
				cmDefaultH : window.innerHeight + 'px',
			}
		},
		cmLineHeightOffset : 4, //value added to font size to create line height
		cmDeadSpaceW : window.innerWidth - AppSettings.common.uiDims.heights.uiBaseline + 'px',
		cmDeadSpaceH : AppSettings.common.uiDims.heights.uiBaseline + 'px', //hover activation for codemirror show/hide
		cmMinDistFromTop : 150,
		cmMinDistFromBottom : 75,
		cmMinDistFromLeft : 300,
		cmMinDistFromRight : 300,
		cmThemes : [
		    'obsidi-dan',
			//'3024-day',
			'3024-night',
			//'base16-dark',
			//'base16-light',
			//'blackboard',
			'cobalt',
			//'eclipse',
			//'elegant',
			//'erlang-dark',
			'lesser-dark',
			//'mbo',
			'mdn-like',
			'midnight',
			'monokai',
			//'neat',
			'neo',
			'night',
			'paraiso-dark',
			//'paraiso-light',
			//'pastel-on-dark',
			'rubyblue',
			//'solarized',
			'the-matrix',
			'tomorrow-night-eighties',
			'twilight',
			'vibrant-ink',
			//'xq-dark',
			//'xq-light'
		],
		uiGridIncr : AppSettings.common.uiDims.heights.uiBaseline,
		baselineGridIncr : 24,
		baselineGridSubdivs : 2,
		baselineGridMaxLinesPerAxis : 500,
		baselineGridOpacity : 0.40,
		baselineGridOpacityAccent : 0.65,
		baselineGridColor1 : '#FF69B4',
		baselineGridColor2 : '#FF69B4',
		baselineGridColorPresets : {
			'Grid palette 1':['#FF69B4','#FF69B4'],
			'Grid palette 2':['#EED92A','#8FE58F']
		},
		cssEditorTextareaRef : 'liveCss',
		autoPersistDataState : false,
		editorContainer : $('#content #editor'),
		editorCount : 3,
		jPickerInitColor : '#123456',
		edgeBarDefaultW : 300,
		screenWidths : {
			1 : {
				'abbr' : 'LG',
				'name' : 'Large',
				'class' : 'lg',
				'width' : '100%'
			},
			2 : {
				'abbr' : 'MD',
				'name' : 'Medium',
				'class' : 'md',
				'width' : '1179px'
			},
			3 : {
				'abbr' : 'SM',
				'name' : 'Small',
				'class' : 'sm',
				'width' : '975px'
			},
			4 : {
				'abbr' : 'XS',
				'name' : 'Extra small',
				'class' : 'xs',
				'width' : '749px'
			},
			5 : {
				'abbr' : 'XXS',
				'name' : 'Extra extra small',
				'class' : 'xxs',
				'width' : '300px'
			}
		},
		jobStateDefaults : {
			'screenWidthId' : 1
		},
		activeJobId : false
	};
	LiveEditor.common.uiCssParser = function(scPtVg){

		if(typeof scPtVg == "undefined"){
			$.each(LiveEditor.common.uiState.data.uiGlobalCss, function(sc,dc){
				$.each(dc, function(pt,vg){
					$('style[id="'+sc+pt+'"]').remove();
					$('<style id="'+sc+pt+'">' + sc + ' { '+pt+': '+vg+' }</style>'+"\n").appendTo('head');
				});
			});
		} else {

			//scDc = [.CodeMirror, font-size, 16px]
			var sc = scPtVg[0];
			var pt = scPtVg[1];
			var vg = scPtVg[2];

			//apply the new value to the object
			if(LiveEditor.common.uiState.data.uiGlobalCss[sc] == null){
				LiveEditor.common.uiState.data.uiGlobalCss[sc] = {};
			}
			LiveEditor.common.uiState.data.uiGlobalCss[sc][pt] = vg;
			$.cookie('LiveEditor.common.uiState',JSON.stringify(LiveEditor.common.uiState));
			$('style[id="'+sc+pt+'"]').remove();
			$('<style id="'+sc+pt+'">' + sc + ' { '+pt+': '+vg+' }</style>'+"\n").appendTo('head');

		}

		/*-DEBUG-B-*/
		if(debuggery){
			var out = '';
			$.each(LiveEditor.common.uiState.data.uiGlobalCss, function(sc,dc){
				$.each(dc, function(pt,vg){
					out += sc + ' {' +pt+': '+vg+'}<br />';
				});
			});
			debug(8,'UI global state',out);
		}
		/*-DEBUG-E-*/

	};
	LiveEditor.common.uiStateManger = function(key,value){
		if(typeof key == "undefined"){
			//get
		} else {
			//set
			LiveEditor.common.uiState.data[key] = value;
			$.cookie('LiveEditor.common.uiState',JSON.stringify(LiveEditor.common.uiState));
		}
		/*-DEBUG-B-*/
		if(debuggery){
			var out = '';
			$.each(LiveEditor.common.uiState.data, function(i,v){
				out += i + v.toSource()+'<br />';
			});
			debug(8,'UI global state',out);
		}
		/*-DEBUG-E-*/
	}
	LiveEditor.iframe = {
		//height : window.innerHeight - AppSettings.common.uiDims.heights.uiBaseline - AppSettings.common.uiDims.heights.valuesPalette,
		createIframe : function(job){
			var uri = uri_maker(job.id);

			if($('#liveEditor'+job['id']).length == 0){
				var iframe = $('<iframe src="'+uri+'" id="liveEditor'+job['id']+'" class="liveEditor" data-job="'+job['id']+'" />');
				LiveEditor.common.editorContainer.append(iframe);
				//iframe.css('height',LiveEditor.iframe.height);
			} else {
				$('#liveEditor'+job['id']).attr('src',uri);
			}
			//LiveEditor.jobManager.jobStatusManager(job['id'],1);
			//return job['id'];
		}
	};

	LiveEditor.layoutTools = {

		deviceSimulator : {
			tool : function(){

				LiveEditor.layoutTools.deviceSimulator.nav();
				$(document).on('click','.screenWidth',function(){
					var jobId = LiveEditor.jobManager.getActiveJobId();
					var screenWidthId = $(this).attr('data-screenWidthId');
					LiveEditor.layoutTools.deviceSimulator.applyScreenWidth(screenWidthId,jobId);
					LiveEditor.jobManager.jobStatesManager(jobId,'screenWidthId',screenWidthId);

					LiveEditor.uiTools.uiResizerApplyWidth(jobId);
					LiveEditor.uiTools.uiResizerApplyHeight(jobId);

					return false;
				});

			},
			nav : function(){

				var out = '<ul id="screenWidths">';
				$.each(LiveEditor.common.screenWidths,function(i,v){
					out += '<li><a class="btn btn-default btn-xs screenWidth '+v['class']+'" id="screenWidth'+i+'" data-screenWidthId="'+i+'" href="#" title="'+v['name']+'">'+v['abbr']+'</a></li>';
				});
				out += '</ul>';
				$('#ctrlSet5').append(out);
				//LiveEditor.layoutTools.deviceSimulator.buildScreenWidthStyles();

			},
			//buildScreenWidthStyles : function(){

				//var styles = "\n<style id='deviceSimulatorStyles'>\n/* DEVICE SIMULATOR STYLES */\n";
				//$.each(LiveEditor.common.screenWidths,function(i,v){
					//var widthVal = parseInt( v['width'].replace('px','') );

					//if(v['abbr'] != 'LG'){
						//styles += 'iframe.'+v['class']+' {width: '+ v['width'] + '; margin-left:-'+ ((widthVal/2) - (AppSettings.common.uiDims.workArea.marginL/2) - (AppSettings.common.uiDims.workArea.marginR/2) - (cmW)) +'px}'+"\n";
						//styles += 'iframe.dormant.'+v['class']+' {height:'+ (window.innerHeight-AppSettings.common.uiDims.heights.topBar-LiveEditor.common.iframeTopOffset-LiveEditor.common.iframeBottomOffset-LiveEditor.common.cmDeadSpaceH - AppSettings.common.uiDims.workArea.marginB - AppSettings.common.uiDims.workArea.marginT) +'px!important; top:'+ (LiveEditor.common.iframeTopOffset+AppSettings.common.uiDims.heights.topBar + AppSettings.common.uiDims.workArea.marginT) +'px;}'+"\n";
					//} else {
						//styles += 'iframe.'+v['class']+' {width: '+v['width']+ '}'+"\n";
						//styles += 'iframe.dormant.'+v['class']+' {height:'+ (window.innerHeight-AppSettings.common.uiDims.heights.topBar-AppSettings.common.uiDims.workArea.marginB - AppSettings.common.uiDims.workArea.marginT) +'px!important}'+"\n";
					//}

				//});
				//styles += "</style>\n\n";
				//$('#deviceSimulatorStyles').remove();
				//$('head').append(styles);

			//},
			applyScreenWidth : function(screenWidthId,jobId){

				var element = '#liveEditor'+jobId;
				$.each(LiveEditor.common.screenWidths,function(i,v){
					$(element).removeClass(v['class']);
				});
				$(element).addClass(LiveEditor.common.screenWidths[screenWidthId]['class']);
				$('a.screenWidth').removeClass('active');
				$('a#screenWidth'+screenWidthId).addClass('active');

				//LiveEditor.uiTools.uiResizerApplyHeight(jobId);

			}
		},
		colorPicker : {
			tool : function(){
				LiveEditor.layoutTools.colorPicker.nav();
				$(document).on('click','.ctrlColorPicker',function(){

					var jobId = LiveEditor.jobManager.getActiveJobId();
/*
					if(LiveEditor.jobManager.jobStates.data[jobId].colorPicker != null){
						if(LiveEditor.jobManager.jobStates.data[jobId].colorPicker.status == null){
							status = 1; //default ON
						} else if(LiveEditor.jobManager.jobStates.data[jobId].colorPicker.status == 0) {
							status = 1; //if last setting was OFF, turn it ON
						} else {
							status = 0; //otherwise the last setting must have been ON so turn it OFF
						}
					} else {
						status = 1;
					}
					LiveEditor.jobManager.jobStatesManager(jobId, 'colorPicker', {
						status : status,
					});*/

					LiveEditor.layoutTools.colorPicker.colorPickerState(jobId);
					return false;
				});
			},
			nav : function(){
				$('#ctrlSet3').append("<div class='ctrl ctrlButton' id='ctrlColorPicker'><a href='#' class='btn btn-default btn-xs ctrlColorPicker' title='Color picker'><span class='glyphpro glyphpro-eyedropper'></span></a></div>");
			},
			colorPickerState : function(jobId){
				if(LiveEditor.jobManager.jobStates.data[jobId].colorPicker != null){
					if(LiveEditor.jobManager.jobStates.data[jobId].colorPicker.status == null){
						status = 1; //default ON
					} else if(LiveEditor.jobManager.jobStates.data[jobId].colorPicker.status == 0) {
						status = 1; //if last setting was OFF, turn it ON
					} else {
						status = 0; //otherwise the last setting must have been ON so turn it OFF
					}
				} else {
					status = 1;
				}
				LiveEditor.jobManager.jobStatesManager(jobId, 'colorPicker', {
					status : status,
				});

				LiveEditor.layoutTools.colorPicker.colorPickerManager(jobId,status);
			},
			colorPickerManager : function(jobId,status){
				loadPicker = true;
				$('span.jPicker').hide();
				if(status == 1){
					$('.ctrlColorPicker').addClass('active');
					$('span.jPicker .Image').trigger('click');
				} else {
					$('.ctrlColorPicker').removeClass('active');
					$('.Ok').trigger('click');
				}
			}
		},
		jobLayoutAssets : {
			tool : function(){
				LiveEditor.layoutTools.jobLayoutAssets.nav();
				$(document).on('click','#assetsColors A',function(){
					var val = $(this).text();
					LiveEditor.layoutTools.inspector.transferContent(val,'around');
					return false;
				});
			},
			nav : function(){
				var dataJson = '{"title":"","content":"","w":600}';
				$('#ctrlSet3').append("<div class='ctrl ctrlButton edgeBarOpener edgeBarL' id='ctrlJobLayoutAssets'><a href='#' class='btn btn-default btn-xs' data-json='"+dataJson+"' title='Assets'><span class='glyphicon glyphicon-th'></span></a></div>");
			}
		},
		inspector : {
			tool : function(){
				LiveEditor.layoutTools.inspector.nav();
				$(document).on('click','#ctrlInspector a',function(){

					var jobId = LiveEditor.jobManager.getActiveJobId();

					if(LiveEditor.jobManager.jobStates.data[jobId].inspector != null){
						if(LiveEditor.jobManager.jobStates.data[jobId].inspector.status == null){
							status = 1; //default ON
						} else if(LiveEditor.jobManager.jobStates.data[jobId].inspector.status == 0) {
							status = 1; //if last setting was OFF, turn it ON
						} else {
							status = 0; //otherwise the last setting must have been ON so turn it OFF
						}
					} else {
						status = 1;
					}
					LiveEditor.jobManager.jobStatesManager(jobId, 'inspector', {
						status : status,
					});

					LiveEditor.layoutTools.inspector.inspectorManager(jobId,status);
					return false;
				});
				$(document).keydown(function (e) {
					LiveEditor.layoutTools.inspector.keyLoadInspector(e);
				});
			},
			nav : function(){
				$('#ctrlSet3').append("<div class='ctrl ctrlButton' id='ctrlInspector'><a href='#' class='btn btn-default btn-xs' title='Inspector'><span class='glyphicon glyphicon-search'></span></a></div>");
			},
			keyLoadInspector : function(e){
				if (e.which === 71 && e.shiftKey && e.altKey) { // Alt + Shift + G
					$("#ctrlInspector a").trigger("click");
			        return false;
			    }
			},
			inspectorManager : function(jobId,status){

				var sourceContents = $('#liveEditor'+jobId).contents();

				if(status == 1){

					//Inspector ON
					sourceContents.find('body #baselineGrid b').css('z-index','-1');
					sourceContents.find('A').each(function(i,v){
						$(this).bind('click',false);
						$(this).unbind(LiveEditor.common.iframeClickRef);
					});
					sourceContents.find('input').each(function(i,v){
						$(this).bind('click',false);
						$(this).unbind(LiveEditor.common.iframeClickRef);
					});
					sourceContents.find('BODY *').each(function(i,v){

						$(this).bind('mouseover', function(event) {
							$(this).css('outline','1px solid '+LiveEditor.common.inspector.outlineColor+'').css('outline-offset','-1px');

							//feedback tool
							inspectorFeedback = {};

							//list parents of highlighted item
							inspectorFeedback.parents = '';
							$(this).parents().reverse().each(function(ii,vv){
								if(vv.tagName.toLowerCase() != 'html'){
									myParentId = $(this).attr('id');
									myParentClass = $(this).attr('class');
									if(myParentId == undefined && myParentClass == undefined){
										myParentTag = vv.tagName;
									} else if(jQuery.inArray(vv.tagName.toLowerCase(),LiveEditor.common.inspector.permittedTags) != -1){
										myParentTag = vv.tagName;
									} else if((LiveEditor.common.inspector.showTags == 1 && jQuery.inArray(vv.tagName.toLowerCase(),LiveEditor.common.inspector.permittedTags) != -1) || vv.tagName.toLowerCase() == 'body'){
										myParentTag = vv.tagName;
									} else {
										myParentTag = '';
									}
									if(myParentId == undefined){myParentId = '';} else {myParentId = '#'+myParentId;}
									if(myParentClass == undefined){myParentClass = '';} else {myParentClass = '.'+myParentClass;}
									myParentClass = myParentClass.replace(/^\s+|\s+$/g, ''); //trim l/r whitespace
									myParentClass = myParentClass.replace(/ /g,'.');
									var str = myParentTag + '' + myParentId + '' + myParentClass;
									if(jQuery.inArray(str.slice(-1),['.','#']) != -1){
										str = str.substring(0, str.length - 1);
									}
									//IAH trying to separa

									/*if(myParentTag != ''){
										newsStr += '<a>'+myParentTag+'</a>';
									}
									if(myParentId != ''){
										newsStr += '<a>#'+myParentId+'</a>';
									}*/
									//newsStr = '';
									str = str.replace(myParentTag,'<a>'+myParentTag+'</a>');
									str = str.replace(myParentId,'<a>'+myParentId+'</a>');
									classes = str.split('.');
									for(i in classes){
										if(classes[i] != ''){
											str = str.replace('.'+classes[i],'<a>.'+classes[i]+'</a>');
										}
									}
									inspectorFeedback.parents += '<span class="wrap"><span class="item itemClick">' + str + ' </span></span>'; //TODO dowe want to insert &gt; automatically?
								}
							});

							//list children of highlighted item
							inspectorFeedback.children = '';
							$(this).children().each(function(ii,vv){
								if(jQuery.inArray(vv.tagName.toLowerCase(),LiveEditor.common.inspector.deniedTags) == -1){
									myChildId = $(this).attr('id');
									myChildClass = $(this).attr('class');
									if(myChildId == undefined && myChildClass == undefined){
										myChildTag = vv.tagName;
									} else if(jQuery.inArray(vv.tagName.toLowerCase(),LiveEditor.common.inspector.permittedTags) != -1){
										myChildTag = vv.tagName;
									} else if(LiveEditor.common.inspector.showTags == 1 && jQuery.inArray(vv.tagName.toLowerCase(),LiveEditor.common.inspector.permittedTags) != -1){
										myChildTag = vv.tagName;
									} else {
										myChildTag = '';
									}
									if(myChildId == undefined){myChildId = '';} else {myChildId = '#'+myChildId;}
									if(myChildClass == undefined){myChildClass = '';} else {myChildClass = '.'+myChildClass;}
									myChildClass = myChildClass.replace(/^\s+|\s+$/g, ''); //trim l/r whitespace
									myChildClass = myChildClass.replace(/ /g,'.');
									//alert('|'+myChildClass+'|');
									var str = myChildTag + '' + myChildId + '' + myChildClass;
									if(jQuery.inArray(str.slice(-1),['.','#']) != -1){
										str = str.substring(0, str.length - 1);
									}
									str = str.replace(myChildTag,'<a>'+myChildTag+'</a>');
									str = str.replace(myChildId,'<a>'+myChildId+'</a>');
									classes = str.split('.');
									for(i in classes){
										if(classes[i] != ''){
											str = str.replace('.'+classes[i],'<a>.'+classes[i]+'</a>');
										}
									}
									inspectorFeedback.children += '<span class="wrap"><span class="item itemChild itemClick">' + str + ' </span></span>'; //TODO dowe want to insert &gt; automatically?
								}
							});

							//list highlighted item
							myId = $(this).attr('id');
							myClass = $(this).attr('class');
							if(myId == undefined && myClass == undefined){
								myTag = v.tagName;
							} else if(jQuery.inArray(v.tagName.toLowerCase(),LiveEditor.common.inspector.permittedTags) != -1){
								myTag = v.tagName;
							} else {
								myTag = '';
							}
							myName = $(this).attr('name');
							myAlt = $(this).attr('alt');
							myTitle = $(this).attr('title');
							myStyle = $(this).attr('style');
							myStyle = myStyle.replace('outline: 1px solid '+LiveEditor.common.inspector.outlineColor+'; outline-offset: -1px;','');
							myWidth = $(this).outerWidth(true);
							myHeight = $(this).outerHeight(true);
							if(myId == undefined){myId = '';} else {myId = '#'+myId;}
							if(myClass == undefined){myClass = '';} else {myClass = '.'+myClass;}
							myClass = myClass.replace(/^\s+|\s+$/g, ''); //trim l/r whitespace
							myClass = myClass.replace(/ /g,'.');

							//build list
							var str = myTag + '' + myId + '' + myClass;
							if(jQuery.inArray(str.slice(-1),['.','#']) != -1){
								str = str.substring(0, str.length - 1);
							}
							str = str.replace(myTag,'<a>'+myTag+'</a>');
							str = str.replace(myId,'<a>'+myId+'</a>');
							classes = str.split('.');
							for(i in classes){
								if(classes[i] != ''){
									str = str.replace('.'+classes[i],'<a>.'+classes[i]+'</a>');
								}
							}
							tagList = '<b class="elInf elInf1" id="phLoadElInfx">';
							tagList += '<span class="elInfParents">' + inspectorFeedback.parents + '</span>';
							tagList += '<span class="elInfItem"><span class="wrap"><span class="item itemClick">' + str +' </span></span></span>';
							tagList += '<ul class="moreInfo">';
							if(myName != undefined){tagList += '<li><span class="moreName">Name:</span><span class="moreDetail">'+myName+'</span></li>';}
							if(myAlt != undefined && myAlt != ''){tagList += '<li><span class="moreName">Alt:</span><span class="moreDetail">'+myAlt+'</span></li>';}
							if(myTitle != undefined && myTitle != ''){tagList += '<li><span class="moreName">Title:</span><span class="moreDetail">'+myTitle+'</span></li>';}
							if(myStyle != undefined && myStyle != ''){tagList += '<li><span class="moreName">Style:</span><span class="moreDetail">'+myStyle+'</span></li>';}
							tagList += '<li><span class="moreName">Width:</span><span class="moreDetail">'+myWidth+'px</span></li>';//TODO round Math.round() causing crash!!
							tagList += '<li><span class="moreName">Height:</span><span class="moreDetail">'+myHeight+'px</span></li>';
							tagList += '</ul>';
							if(inspectorFeedback.children != ''){tagList += '<span class="elInfChildren">' + inspectorFeedback.children + '</span>';}
							tagList += '</b>';
							$('BODY').prepend(tagList);

							return false;//prevents parent elements being returned
						});
						$(this).bind('click.elInf2', function(event) {

							tagListLast = '<b class="elInf elInf2" id="phLoadElInfx">';
							tagListLast += '<a href="" onClick="$(this).parent().remove(); return false;" class="butt close"></a>';
							tagListLast += '<a href="" onClick="LiveEditor.layoutTools.inspector.transferContent($(this).parent().find(\'span.item\').text() + \'{}\'); return false;" class="butt all" title="Transfer all items"></a>';
							tagListLast += $('.elInf1').html();
							tagListLast += '</b>';
							$('BODY .elInf2').empty().remove();
							$('BODY').prepend(tagListLast);

							$('.elInf2 span.itemClick').each(function(){
								var stuff = '<a href="" onClick="LiveEditor.layoutTools.inspector.transferContent($(this).parent(\'.wrap\').children(\'span.item\').text()); return false;" class="butt transfer" title="Transfer this item"></a>'+"\n";
								$(this).before(stuff);
							});

							$('.elInf2 span.elInfParents span.itemClick').each(function(){
								var stuff = '<a href="" onClick="LiveEditor.layoutTools.inspector.transferContent(LiveEditor.layoutTools.inspector.getItemsUpto($(this))); return false;" class="butt transferBefore" title="Transfer this item and all items before it"></a>'+"\n";
								stuff += '<a href="" onClick="LiveEditor.layoutTools.inspector.transferContent(LiveEditor.layoutTools.inspector.getItemsFrom($(this))); return false;" class="butt transferAfter" title="Transfer this item and all items after it"></a>'+"\n";
								$(this).before(stuff);
							});

							$('.elInf2 span.elInfItem span.itemClick').each(function(){
								var stuff = '<a href="" onClick="LiveEditor.layoutTools.inspector.transferContent(LiveEditor.layoutTools.inspector.getParents($(this))); return false;" class="butt transferBefore" title="Transfer this item and all items before it"></a>'+"\n";
								$(this).before(stuff);
							});

						});

						$(this).bind('mouseout', function(event) {
							$('BODY .elInf1').empty().remove();
							$(this).css('outline','');
							$(this).css('outline-offset','');
						});

					});

					//Exclude these from inspector
					sourceContents.find('BODY #baselineGrid').unbind('mouseover');
					sourceContents.find('BODY #baselineGrid B').each(function(i,v){
						$(this).unbind('mouseover');
					});

				} else {
					//Inspector OFF
					sourceContents.find('BODY #baselineGrid b').css('z-index','');
					sourceContents.find('A').each(function(i,v){
						$(this).unbind('click',false);
						$(this).bind('click.ifrLive', function(event) {
							//$('#ifrLive').hide();
							//$('#ifrLiveLoader').show();
						});
					});
					sourceContents.find('input').each(function(i,v){
						$(this).unbind('click',false);
						$(this).bind('click.ifrLive', function(event) {
							//$('#ifrLive').hide();
							//$('#ifrLiveLoader').show();
						});
					});
					sourceContents.find('BODY *').each(function(i,v){
						$(this).unbind('mouseover');
						$(this).unbind('click.elInf2');
					});
					$('BODY .elInf2').empty().remove();

				}

				if(status == 1){
					$('#ctrlInspector a').addClass('active');
				} else {
					$('#ctrlInspector a').removeClass('active');
				}
			},
			transferContent : function(content,postTranferCursorPos){
				if(typeof postTranferCursorPos == "undefined"){
					var postTranferCursorPos = LiveEditor.common.inspector.postTranferCursorPos;
				}
				myCodeMirror.cm.applyContent(myCodeMirror.cm,content,postTranferCursorPos);
			},
			getItemsUpto : function(srcItem){
				var items = [];
				srcItem.parent('.wrap').prevAll().children('span.item').each(function(){
					items.push($(this).text());
				});
				items.reverse();
				items.push(srcItem.parent('.wrap').children('span.item').text());
				return items.join('');
			},
			getItemsFrom : function(srcItem){
				var items = [];
				items.push(srcItem.parent('.wrap').children('span.item').text());
				srcItem.parent('.wrap').nextAll().children('span.item').each(function(){
					items.push($(this).text());
				});
				items.push($('span.elInfItem .item').text());
				return items.join('');
			},
			getParents : function(srcItem){
				var items = [];
				$('span.elInfParents .wrap').each(function(){
					items.push($(this).children('span.item').text());
				});
				items.push(srcItem.parent('.wrap').children('span.item').text());
				return items.join('');
			}
		},
		baselineGrid : {
			tool : function(){
				LiveEditor.layoutTools.baselineGrid.nav();
				//grid status
				$(document).on('click','#ctrlBaselineGridX a',function(){
					var jobId = LiveEditor.jobManager.getActiveJobId();
					var state = LiveEditor.jobManager.getJobState(jobId,'baselineGridX');
					if(state == 0){
						LiveEditor.layoutTools.baselineGrid.apply(jobId,1,'x');
					} else {
						LiveEditor.layoutTools.baselineGrid.apply(jobId,0,'x');
					}
					return false;
				});
				$(document).on('click','#ctrlBaselineGridY a',function(){
					var jobId = LiveEditor.jobManager.getActiveJobId();
					var state = LiveEditor.jobManager.getJobState(jobId,'baselineGridY');
					if(state == 0){
						LiveEditor.layoutTools.baselineGrid.apply(jobId,1,'y');
					} else {
						LiveEditor.layoutTools.baselineGrid.apply(jobId,0,'y');
					}
					return false;
				});
				//grid size
				$(document).on('click','.ctrlUiBaselineGridSize a',function(){

					var jobId = LiveEditor.jobManager.getActiveJobId();
					var currentValue = LiveEditor.jobManager.getJobState(jobId,'baselineGridSize');
					if(currentValue == 0){currentValue = LiveEditor.common.baselineGridIncr;}
					//var iframe = $('#liveEditor'+jobId);
					//iframe.contents().find('#baselineGrid').remove();
					//iframe.contents().find('#baselineGridStyles').remove();
					if($(this).hasClass('increase')){
						currentValue++;
					} else if($(this).hasClass('decrease')) {
						currentValue--;
					}
					if(currentValue <= 10){currentValue = 10;}
					LiveEditor.jobManager.jobStatesManager(jobId,'baselineGridSize',currentValue);
					LiveEditor.layoutTools.baselineGrid.apply(jobId,1,'x',true);
					LiveEditor.layoutTools.baselineGrid.apply(jobId,1,'y',true);
					flash.manager('success','Baseline grid set to '+currentValue+'px',true);
					return false;
				});
				//grid size
				$(document).on('keypress','.baselineGridColor input',function(){

					var jobId = LiveEditor.jobManager.getActiveJobId();
					if($(this).hasClass('baselineGridColor1')){
						LiveEditor.jobManager.jobStatesManager(jobId,'baselineGridColor1',$(this).val());
					} else if($(this).hasClass('baselineGridColor2')) {
						LiveEditor.jobManager.jobStatesManager(jobId,'baselineGridColor2',$(this).val());
					}
					LiveEditor.layoutTools.baselineGrid.apply(jobId,1,'x',true);
					LiveEditor.layoutTools.baselineGrid.apply(jobId,1,'y',true);
					flash.manager('success','Baseline color set to '+$(this).val()+'',true);
					
					return false;
					
				});
			},
			nav : function(){
				$('#ctrlSet3').append("<div class='ctrl ctrlButton' id='ctrlBaselineGridX'><a href='#' class='btn btn-default btn-xs' title='Baseline grid'><span class='glyphpro glyphpro-show_lines'>x</span></a></div>");
				$('#ctrlSet3').append("<div class='ctrl ctrlButton' id='ctrlBaselineGridY'><a href='#' class='btn btn-default btn-xs' title='Baseline grid'><span class='glyphpro glyphpro-show_lines'>y</span></a></div>");
			},
			apply : function(jobId,state,axis,reset){

				var sourceContents = $('#liveEditor'+jobId).contents();
				
				if(typeof reset != "undefined"){
					sourceContents.find('#baselineGrid ' + axis).remove();
					sourceContents.find('#baselineGridStyles').remove();
				}
				if(state == 1){
				
					var baselineGridColor1 = LiveEditor.jobManager.getJobState(jobId,'baselineGridColor1');
					var baselineGridColor2 = LiveEditor.jobManager.getJobState(jobId,'baselineGridColor2');
					if(baselineGridColor1 == 0){baselineGridColor1 = LiveEditor.common.baselineGridColor1;}
					if(baselineGridColor2 == 0){baselineGridColor2 = LiveEditor.common.baselineGridColor2;}
					
					//styles
					if(sourceContents.find('style#baselineGridStyles').length == 0){
						var styles = "<style id='baselineGridStyles'>/*baseline grid styles*/\nl.x {left:0; width:100%}\nl.y {top:0; height:"+(sourceContents.height())+"px}\nl.x,l.y {position:absolute; margin:0; padding:0; opacity:"+(LiveEditor.common.baselineGridOpacity)+"}\nl.x {border-bottom:1px solid "+baselineGridColor1+";}\nl.y{border-left:1px solid "+baselineGridColor1+";}l.x.a.o,l.y.a.o {opacity:"+(LiveEditor.common.baselineGridOpacityAccent)+"}\nl.x.b{border-bottom:1px dotted "+baselineGridColor2+"}\nl.y.b {border-left:1px dotted "+baselineGridColor2+"}\nl:hover {opacity:0.9!important}\nl.y.a.oo{opacity:1}\nm {margin:0; padding:0; position:absolute; background-color:transparent; color:#FFF; font-size:9px; height:11px; font-family:consolas; text-align:right; display:none; opacity:0.7; text-shadow:1px 1px #333}\nHTML:hover m {display:block}\nx m {left:0; width:22px; margin-top:-5px;}\ny m {top:0; margin-left:-5px; margin-top:5px; width:11px; transform: rotate(270deg);</style>";
						sourceContents.find('body').append(styles);
					}
					
					
					//grids
					if( (sourceContents.find('#baselineGrid ' + axis).length == 0) ){

						var value = LiveEditor.jobManager.getJobState(jobId,'baselineGridSize');
						if(value == 0){
							value = LiveEditor.common.baselineGridIncr;
						}
						
						var maxLines = LiveEditor.common.baselineGridMaxLinesPerAxis / LiveEditor.common.baselineGridSubdivs;
						var lines = {x:'',y:''};
						var meas = {x:'',y:''};
						
						// X grid
						if(axis == 'x'){ 

							for(i = 1; i < maxLines; i++){

								var htmlClass = '';

								var x1pos = (i * value) - 1;
								var x2pos = ((i * value + (value / LiveEditor.common.baselineGridSubdivs)) - value) - 1;

								if(i % 5 == 0){
									htmlClass = 'o';
								}

								if(x1pos < sourceContents.height()){
									
									lines[axis] += '<l class="x a '+htmlClass+'" style="top:'+ x1pos +'px"></l>';
									meas[axis] += '<m style="top:'+ x1pos +'px">'+(x1pos+1)+'</m>';
									
								} else {
									i = maxLines;
								}
								if(LiveEditor.common.baselineGridSubdivs > 1){
									if(x2pos < sourceContents.height()){
										lines[axis] += '<l class="x b" style="top:'+ x2pos +'px"></l>';
									} else {
										i = maxLines;
									}
								}
							
							}
							
						}
						
						// Y grid
						if(axis == 'y'){ 
						
							var yOffset = (sourceContents.width() / 2);

							lines[axis] += '<l class="y a oo" style="left:'+ yOffset +'px"></l>';
							meas[axis] += '<m style="left:'+ yOffset +'px">0</m>';
						
							for(i = 1; i < maxLines; i++){

								var htmlClass = '';

								var y1posA = (yOffset + (i * value));
								var y1posB = (yOffset - (i * value));
								var y2posA = yOffset + ((i * value + (value / LiveEditor.common.baselineGridSubdivs)) - value);
								var y2posB = yOffset - ((i * value + (value / LiveEditor.common.baselineGridSubdivs)) - value);

								if(i % 5 == 0){
									htmlClass = 'o';
								}
								if(y1posA < sourceContents.width()){
									lines[axis] += '<l class="y a '+htmlClass+'" style="left:'+ y1posA +'px"></l>';
									lines[axis] += '<l class="y a '+htmlClass+'" style="left:'+ y1posB +'px"></l>';
									
									if(y1posA < (sourceContents.width() - value)){
										meas[axis] += '<m style="left:'+ y1posA +'px">'+ (y1posA-yOffset) +'</m>';
									}
									if(y1posA < (sourceContents.width() - value)){
										meas[axis] += '<m style="left:'+ y1posB +'px">'+ -(y1posB-yOffset) +'</m>';
									}
									
								}
								if(y2posA < sourceContents.width()){
									lines[axis] += '<l class="y b '+htmlClass+'" style="left:'+ y2posA +'px"></l>';
									lines[axis] += '<l class="y b '+htmlClass+'" style="left:'+ y2posB +'px"></l>';	
								}

							}
						
						}

						var html = '';
						if(sourceContents.find('#baselineGrid').length == 0){
							html += '<div id="baselineGrid">';
							html += '<'+axis+'>'+ lines[axis] + meas[axis] +'</'+axis+'>';
							html += '</div>';
							sourceContents.find('body').append(html);
						} else {
							html += '<'+axis+'>'+ lines[axis] + meas[axis] +'</'+axis+'>';
							sourceContents.find('#baselineGrid').append(html);
						}

						
						
					} else {
						sourceContents.find('body #baselineGrid ' + axis).show();
					}
					if(axis == 'x'){
						LiveEditor.jobManager.jobStatesManager(jobId,'baselineGridX',1);
						$('#ctrlBaselineGridX a').addClass('active'); 
					}
					if(axis == 'y'){
						LiveEditor.jobManager.jobStatesManager(jobId,'baselineGridY',1);
						$('#ctrlBaselineGridY a').addClass('active'); 
					}
						
				} else {
				
					//turn it off
					if(axis == 'x'){ 
						LiveEditor.jobManager.jobStatesManager(jobId,'baselineGridX',0);
						sourceContents.find('body #baselineGrid x').hide();
						$('#ctrlBaselineGridX a').removeClass('active');
					}
					if(axis == 'y'){ 
						LiveEditor.jobManager.jobStatesManager(jobId,'baselineGridY',0);
						sourceContents.find('body #baselineGrid y').hide();
						$('#ctrlBaselineGridY a').removeClass('active');
					}
					
				}


			}
		}

	};

	LiveEditor.remoteTools = {
		reloadCurrent :{
			tool : function(){
				LiveEditor.remoteTools.reloadCurrent.nav();
				$(document).on('click','#ctrlReloadCurrent a',function(){
					var jobId = LiveEditor.jobManager.getActiveJobId();
					var iframe = $('#liveEditor'+jobId);
					iframe.attr('src', iframe.contents().get(0).location.href );
					return false;
				});
			},
			nav : function(){
				$('#ctrlSet2').append("<div class='ctrl ctrlButton' id='ctrlReloadCurrent'><a href='#' class='btn btn-default btn-xs' title='Reload current page'><span class='glyphicon glyphicon-refresh'></span></a></div>");
			},
		},
		useCached :{
			tool : function(){
				LiveEditor.remoteTools.useCached.nav();
				$(document).on('click','.ctrlUseCached a',function(){
					var jobId = LiveEditor.jobManager.getActiveJobId();
					var state = LiveEditor.jobManager.getJobState(jobId,'cached');
					if(state == 0){
						LiveEditor.remoteTools.useCached.apply(jobId,1);
					} else {
						LiveEditor.remoteTools.useCached.apply(jobId,0);
					}
					return false;
				});
			},
			nav : function(){
				//$('#ctrlSet2').append("<div class='ctrl ctrlButton ctrlUseCached'><a href='#' class='btn btn-default btn-xs' title='Toggle live/cached site'><span class='glyphicon glyphicon-camera'></span></a></div>");
			},
			apply : function(jobId,state){
				if (state == 0){
					$('BODY').removeClass('useCached');
					$('#ctrlUseCached a').removeClass('active');
					$('li#navJob'+jobId+' a.navJobCached').removeClass('active');
					LiveEditor.jobManager.jobStatesManager(jobId,'cached',state);
				} else if (state == 1){
					$('BODY').addClass('useCached');
					$('#ctrlUseCached a').addClass('active');
					$('li#navJob'+jobId+' a.navJobCached').addClass('active');
					LiveEditor.jobManager.jobStatesManager(jobId,'cached',state);
				}
				
				uri = uri_maker(jobId);
				if(uri){
					var iframe = $('#liveEditor'+jobId);
					iframe.attr('src', uri );
				} else {
					$('BODY').removeClass('useCached');
					$('#ctrlUseCached a').removeClass('active');
					$('li#navJob'+jobId+' a.navJobCached').removeClass('active');
				}
			}
		},
		newWindowCurrent :{
			tool : function(){
				LiveEditor.remoteTools.newWindowCurrent.nav();
				$(document).on('click','#ctrlNewWindowCurrent a',function(){
					var jobId = LiveEditor.jobManager.getActiveJobId();
					var iframe = $('#liveEditor'+jobId);
					var myUrl = iframe.contents().get(0).location.href;
					myUrl = myUrl.replace(LiveEditor.common.paths.proxyUrlPrefix,'http://');//remove proxy part of the url
					window.open(myUrl,'newWindow');
					return false;
				});
			},
			nav : function(){
				$('#ctrlSet2').append("<div class='ctrl ctrlButton' id='ctrlNewWindowCurrent'><a href='#' class='btn btn-default btn-xs' title='Open current page in new window'><span class='glyphicon glyphicon-new-window'></span></a></div>");
			},
		},
		customUri : {
			tool : function(){

				LiveEditor.remoteTools.customUri.nav();
				$(document).on('click','#saveCustomUri',function(){
					LiveEditor.remoteTools.customUri.applyUri( $(this).prev().val() );
					return false;
				});
				$(document).on('click','.historyLink',function(){
					LiveEditor.remoteTools.customUri.applyUri( $(this).attr('href') );
					return false;
				});
				$(document).on('click','.historyDelete',function(){
					var jobId = $(this).attr('jobId');
					var historyId = $(this).attr('historyId');
					LiveEditor.jobManager.jobStatesManager(jobId,'history',null,historyId);
					$(this).parent().fadeOut();
					return false;
				});

			},
			nav : function(){
				//TODO not dynamic anough - if i add a new link , it is only shown in th history after a page refresh
				//Look at on open modal or something like that
				//i think this modal needs to be created and destroyed when actioned so it is made with current data
				var activeJobId = LiveEditor.jobManager.getActiveJobId();
				var urlPrefix = 'http://'+LiveEditor.jobManager.jobs[activeJobId].url+'/';
				var content = '<label>'+urlPrefix+'<input class="customUri" placeholder="" /><a href="#" id="saveCustomUri" class="btn btn-xs btn-default floatR"><span class="glyphicon glyphicon-circle-arrow-right"></span></a></label>';
				if(typeof LiveEditor.jobManager.jobStates.data[activeJobId]['history'] != undefined){
					content += '<ul class="ctrlList">';
					for(i in LiveEditor.jobManager.jobStates.data[activeJobId]['history']){
						content += '<li>';
						content += '<a href="'+LiveEditor.jobManager.jobStates.data[jobId]['history'][i]+'" class="historyLink">' + urlPrefix + LiveEditor.jobManager.jobStates.data[jobId]['history'][i]+'</a>';
						content += '<span class="actions">';
						content += '<a class="historyDelete" jobId="'+activeJobId+'" historyId="'+i+'"><span class="glyphicon glyphicon-trash"></span></a>';
						content += '</span>';
						content += '</li>';
					}
					content += '</ul>';
				}
				//var dataJson = '{"content":'+JSON.stringify(content)+',"element":{"windowWidth":"400px"}}';
				//$('#ctrlSet2').append("<div class='ctrl ctrlButton' id='ctrlCustomUri'><a data-toggle='modal' href='#modal' id='modal' class='btn btn-default btn-xs openerModalAjax' data-json='"+dataJson+"' title='Load URL'><span class='glyphicon glyphicon-link'></span></a></div>");
				var dataJson = '{"title":"URI Manager","content":'+JSON.stringify(content)+',"w":400}';
				//$('#ctrlSet2').append("<div class='ctrl ctrlButton edgeBarOpener edgeBarL' id='ctrlCustomUri'><a href='#' class='btn btn-default btn-xs' data-json='"+dataJson+"' title='Load URL'><span class='glyphicon glyphicon-link'></span></a></div>");

			},
			applyUri : function(value){
				var jobId = LiveEditor.jobManager.getActiveJobId();
				value = value.replace('/'+LiveEditor.jobManager.jobs[jobId].url+'/','');
				value = value.replace(LiveEditor.common.paths.proxyUrlPrefix,'');
				LiveEditor.jobManager.jobStatesManager(jobId,'default_uri',value);
				LiveEditor.iframe.createIframe(LiveEditor.jobManager.jobs[jobId]);
			}
		},
		cssCodeManager : {
			tool : function(){

				LiveEditor.remoteTools.cssCodeManager.nav();
				$(document).on('click','.ctrlCssCodeManager a',function(){
					if($(this).hasClass('ftp')){
						css_progress_manager( LiveEditor.jobManager.getActiveJobId(), 'ftp' );
					} else if($(this).hasClass('backup')){
						css_backup_manager( LiveEditor.jobManager.getActiveJobId() );
					} else {
						css_progress_manager( LiveEditor.jobManager.getActiveJobId() );
					}
					//IAH mo create a backup loader tool in a flyout menu which allows us to preview code
					//LiveEditor.setWorkArea({marginL:250});
					return false;
				});
				$(document).on('click','#ctrlLoadProgress a',function(){
					if(confirm("Publishing stored code may overwrite someone elses changes\n\nAre you sure you want to load stored code?")){
						css_progress_manager( LiveEditor.jobManager.getActiveJobId(), 'r' );
					}
					return false;
				});

			},
			nav : function(){
				$('#ctrlSet0').append("<div class='ctrl ctrlButton ctrlCssCodeManager'><a href='#' class='btn btn-default btn-xs ftp' title='Save &amp; publish'><span class='glyphicon glyphicon-cloud-upload'></span></a></div>");
				$('#ctrlSet0').append("<div class='ctrl ctrlButton ctrlCssCodeManager'><a href='#' class='btn btn-default btn-xs' title='Save progress'><span class='glyphicon glyphicon-floppy-disk'></span></a></div>");
				$('#ctrlSet0').append("<div class='ctrl ctrlButton' id='ctrlLoadProgress'><a href='#' class='btn btn-default btn-xs' title='Load progress'><span class='glyphicon glyphicon-folder-open'></span></a></div>");
				//$('#ctrlSet0').append("<div class='ctrl ctrlButton ctrlCssCodeManager'><a href='#' class='btn btn-default btn-xs backup' title='Create backup'><span class='glyphicon glyphicon-hdd'></span></a></div>");
			}
		}
	};
	LiveEditor.uiTools = {
		edgeBar : {
			tool : function(){
				/*
					data = {
						edge:t/b/l/r
						w:width(edge.l/edge.r)
						h:height(edge.t/edge.b)
						title : (string)
						content:(object){url}
						content:(string)
					}
					.edgeBarOpener must also have a class of the parent id
					e.g. '.edgeBarOpener.edgeBarL' is an opener for a left edgeBar

				*/
				$(document).on('click','.edgeBarOpener a',function(){
					var data = {};
					try{ data = JSON.parse( $(this).attr('data-json') ); } catch(e) { }
					if(isObject(data.content)){
						if(data.content.url != null){
							$.get(data.content.url, function( content ){
								data.content = content;
							});
						}
					} else if(typeof data.content == "undefined") {
						data.content = 'NO CONTENT SPECIFIED!!';
					}
					LiveEditor.uiTools.edgeBar.manager(data,$(this));
					return false;
				});
				$(document).on('click','a.edgeBarClose',function(){
					var edgeBarType = $(this).parent().attr('id');
					if(edgeBarType == 'edgeBarT'){	LiveEditor.setWorkArea({marginT:null});	}
					if(edgeBarType == 'edgeBarR'){	LiveEditor.setWorkArea({marginR:null});	}
					if(edgeBarType == 'edgeBarB'){	LiveEditor.setWorkArea({marginB:null}); }
					if(edgeBarType == 'edgeBarL'){	LiveEditor.setWorkArea({marginL:null});	}
					$('#'+edgeBarType).hide();
					$('.edgeBarOpener.'+edgeBarType+' a').removeClass('active');
					return false;
				});
			},
			manager : function(data,opener){
				if(data.edge == null){ data.edge = 'l'; }
				if(data.w == null && data.edge == 'l'){ data.w = LiveEditor.common.edgeBarDefaultW; }
				var content = '';
				if(data.title != null) {
					content += '<h2>'+data.title+'</h2>';
				}
				content += data.content;

				lastStatus = 'inactive';
				if(opener.hasClass('active')){
					lastStatus = 'active';
				}
				$('.edgeBarOpener a').removeClass('active');
				if(data.edge == 'l'){
					var edgeBar = $('#edgeBarL');
					LiveEditor.setWorkArea({marginL:null});
					opener.removeClass('active');
					$('#edgeBarL #edgeBarInner').empty();
					$('#edgeBarL').hide();

					if(lastStatus != 'active'){
						LiveEditor.setWorkArea({marginL:data.w});
						edgeBar.css({'width':(data.w-LiveEditor.common.uiGridIncr)+'px','top': 0,'left':LiveEditor.common.uiGridIncr +'px','height':'100%'});
						opener.addClass('active');
						$('#edgeBarL').show();
						$('#edgeBarL #edgeBarInner').html(content);
						
					}

				}

				//Customisations for each edgeBar applied post-content population
				
				//ctrlUiSettings
				if(opener.parent().attr('id') == 'ctrlUiSettings'){
					var baselineGridColor1 = LiveEditor.jobManager.getJobState(jobId,'baselineGridColor1');
					var baselineGridColor2 = LiveEditor.jobManager.getJobState(jobId,'baselineGridColor2');
					if(baselineGridColor1 == 0){baselineGridColor1 = LiveEditor.common.baselineGridColor1;}
					if(baselineGridColor2 == 0){baselineGridColor2 = LiveEditor.common.baselineGridColor2;}
					$('input.baselineGridColor1').val(baselineGridColor1);
					$('input.baselineGridColor2').val(baselineGridColor2);
					bind_ui_color_picker();
				}
				
				//ctrlJobList
				LiveEditor.jobTools.jobList.applyContentJobListNav();
				
				//ctrlJobLayoutAssets
				if(opener.parent().attr('id') == 'ctrlJobLayoutAssets'){
					var url = APP_BASE_URI+'live_editors/extract_assets/'+jobId;
					var content = '';
					$.get(url, function( output ){
						output = JSON.parse(output);
						content += '<div class="assets" id="assetsColors">';
						content += '<h3>Colours</h3>';
						for(i in output){
							if(i != 'variables'){
								if(Object.keys(output[i]).length > 0){
									content += '<h4>'+i.toUpperCase()+'</h4>';
									for(ii in output[i]){
										var color = output[i][ii];
										content += '<a class="c" style="background-color:'+color+'">'+color+'</a>';
									}
								}
							} else {
								if(Object.keys(output[i]).length > 0){
									content += '<h4>Variables</h4>';
									for(ii in output[i]){
										var variable = output[i][ii][0];
										var value = output[i][ii][1].replace(/"/g,'');
										content += '<a class="c" style="background-color:'+value+'" title="'+value+'">'+variable+'</a>';
									}
								}
							}
						}
						content += '</div>';
					});
					$('#edgeBarL #edgeBarInner').html(content);
				}

				//ctrlBackupManager
				if(opener.parent().attr('id') == 'ctrlBackupManager'){
					var url = APP_BASE_URI+'live_editors/backup_manager/'+jobId+'/r';
					var content = '';
					$.get(url, function( output ){
						output = JSON.parse(output);
						content += '<div class="backups" id="backups">';
						content += '<h3>Backup Manager</h3>';
						if(Object.keys(output).length > 0){

							content += '<h4>Available backups for '+LiveEditor.jobManager.jobs[jobId].name+'</h4>';
							content += '<ul id="availableBackups" class="ctrlList">';
							for(i in output){
								content += '<li>';
								content += '<span class="info"><span>'+(parseInt(i)+1)+'.</span>'+LiveEditor.jobManager.jobs[jobId].name+' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '+output[i]['str']+'</span>';
								content += '<span class="actions">';
								content += '<a href="#" data-key="'+output[i]['key']+'" class="restoreBackup" title="Restore backup"><span class="glyphicon glyphicon-play-circle"></span></a>';
								content += '<a href="#" data-key="'+output[i]['key']+'" class="deleteBackup" title="Delete backup"><span class="glyphicon glyphicon-trash"></span></a>';
								content += '</span>';								
								content += '</li>';
							}
							content += '</ul>';
						} else {

							content += '<p>There are currently no backups available for '+LiveEditor.jobManager.jobs[jobId].name+'</p>';
							
						}

						content += '<ul id="backupManager" class="btnList">';
							content += '<li>';
							content += '<a href="#" class="createBackup sbtn sbtn-default"><span class="glyphicon glyphicon-floppy-save"></span> create new backup</a>';
							content += '</li>';
							if(Object.keys(output).length > 0){
								content += '<li>';
								content += '<a href="#" class="deleteAllBackups sbtn sbtn-default"><span class="glyphicon glyphicon-trash"></span> delete all backups</a>';
								content += '</li>';
							}
						content += '</ul>';
						
						content += '</div>';
					});
					$('#edgeBarL #edgeBarInner').html(content);
				}
				
				return false;
			}
		},
		layoutMode : {
			tool : function(){
				LiveEditor.uiTools.layoutMode.nav();
				$(document).on('click','#ctrlLayoutMode a',function(){
					var jobId = LiveEditor.jobManager.getActiveJobId();
					var layoutMode = LiveEditor.jobManager.getJobState(jobId,'layoutMode');
					if (layoutMode == 0){

						layoutMode = 1;
						$('body').removeClass('layoutMode0');
						$('body').addClass('layoutMode1');
						LiveEditor.jobManager.jobStatesManager(jobId,'layoutMode',1);
						$(this).addClass('active');

					} else if (layoutMode == 1){

						layoutMode = 0;
						$('body').removeClass('layoutMode1');
						$('body').addClass('layoutMode0');
						LiveEditor.jobManager.jobStatesManager(jobId,'layoutMode',0);
						$(this).removeClass('active');

					}
					//var w = LiveEditor.common.layoutModes[layoutMode].cmDefaultW;
					//var h = LiveEditor.common.layoutModes[layoutMode].cmDefaultH;
					myCodeMirror.cm.switchDims(layoutMode);
					myCodeMirror.cm.deadSpace(layoutMode);
					LiveEditor.setWorkArea();
					return false;
				});
			},
			nav : function(){
				$('#ctrlSet4').append("<div class='ctrl ctrlButton' id='ctrlLayoutMode'><a href='#' class='btn btn-default btn-xs' title='Switch layout'><span class='glyphicon glyphicon-pause'></span></a></div>");
			}
		},
		codeMirrorPin : {
			tool : function(){
				LiveEditor.uiTools.codeMirrorPin.nav();
				$(document).on('click','#ctrlCodeMirrorPin a',function(){
					var jobId = LiveEditor.jobManager.getActiveJobId();
					var codeMirrorPinStatus = LiveEditor.jobManager.getJobState(jobId,'codeMirrorPin');
					if (codeMirrorPinStatus == 0){
						//LiveEditor.uiTools.uiCmStateManager($('.CodeMirror'),'mouseover');
						LiveEditor.uiTools.uiCmShow($('.CodeMirror'));
						LiveEditor.jobManager.jobStatesManager(jobId,'codeMirrorPin',1);
						$(this).addClass('active');
					} else if (codeMirrorPinStatus == 1){
						LiveEditor.uiTools.uiCmHide($('.CodeMirror'));
						LiveEditor.jobManager.jobStatesManager(jobId,'codeMirrorPin',0);
						$(this).removeClass('active');
					}

					return false;
				});
				$(document).keydown(function (e) {
					LiveEditor.uiTools.codeMirrorPin.keyTogglePin(e);
				});
			},
			nav : function(){
				$('#ctrlSet4').append("<div class='ctrl ctrlButton' id='ctrlCodeMirrorPin'><a href='#' class='btn btn-default btn-xs' title='Pin editor'><span class='glyphicon glyphicon-pushpin'></span></a></div>");
			},
			keyTogglePin : function(e){
				if (e.which === 82 && e.shiftKey && e.altKey) { // Alt + Shift + E
					$("#ctrlCodeMirrorPin a").trigger("click");
			        return false;
			    }
			},
		},
		codeMirrorFontSize : {
			tool : function(){
				//LiveEditor.uiTools.codeMirrorFontSize.nav();
				$(document).on('click','.ctrlCodeMirrorFontSize a',function(){
					var currentValue = LiveEditor.common.uiState.data.uiGlobalCss['.CodeMirror']['font-size'];
					currentValue = parseInt( currentValue.replace("px",'') );
					if($(this).hasClass('increase')){
						currentValue++;
						LiveEditor.common.uiCssParser(['.CodeMirror','font-size',currentValue + 'px']);
						LiveEditor.common.uiCssParser(['.CodeMirror','line-height',(currentValue + LiveEditor.common.cmLineHeightOffset) + 'px']);
					} else if(currentValue > 1) {
						currentValue--;
						LiveEditor.common.uiCssParser(['.CodeMirror','font-size',currentValue + 'px']);
						LiveEditor.common.uiCssParser(['.CodeMirror','line-height',(currentValue + LiveEditor.common.cmLineHeightOffset) + 'px']);
					}
					myCodeMirror = new LiveEditor.codeMirror.init( LiveEditor.jobManager.getActiveJobId() );
					LiveEditor.uiTools.uiResizerApplyHeight(jobId);
					LiveEditor.uiTools.uiResizerApplyWidth(jobId);
					flash.manager('success','Font size set to '+currentValue+'px',true);
					return false;
				});
			},
			//nav : function(){
				//$('#ctrlSet4').append("<div class='ctrl ctrlButton ctrlCodeMirrorFontSize'><a href='#' class='btn btn-default btn-xs increase' title='Increase font size'><span class='glyphpro glyphpro-text_bigger'></span></a></div>");
				//$('#ctrlSet4').append("<div class='ctrl ctrlButton ctrlCodeMirrorFontSize'><a href='#' class='btn btn-default btn-xs decrease' title='Decrease font size'><span class='glyphpro glyphpro-text_smaller'></span></a></div>");
			//}
		},
		codeMirrorTheme : {
			tool : function(){
				//LiveEditor.uiTools.codeMirrorTheme.nav();
				$(document).on('click','#ctrlCodeMirrorTheme a',function(){
					var jobId = LiveEditor.jobManager.getActiveJobId();
					var themeNo = LiveEditor.common.uiState.data.cmDefaultTheme;
					var themeLength = Object.keys(LiveEditor.common.cmThemes).length - 1;
					if(themeNo == themeLength){
						themeNo = -1;
					}
					$.each(LiveEditor.common.cmThemes, function(i,v){
						if(i <= themeNo){return;}
						LiveEditor.common.uiStateManger('cmDefaultTheme',i);
						myCodeMirror = new LiveEditor.codeMirror.init( jobId );
						LiveEditor.uiTools.uiResizerApplyHeight(jobId);
						LiveEditor.uiTools.uiResizerApplyWidth(jobId);
						flash.manager('success','Code theme set to '+LiveEditor.common.cmThemes[i].charAt(0).toUpperCase() + LiveEditor.common.cmThemes[i].replace("-"," ").slice(1),true);
						/*-DEBUG-B-*/
						if(debuggery){
							debug(9,'Misc.','CM theme: ' + v);
						}
						/*-DEBUG-E-*/
						return false;
					});
					return false;
				});
			},
			//nav : function(){
			//	$('#ctrlSet4').append("<div class='ctrl ctrlButton' id='ctrlCodeMirrorTheme'><a href='#' class='btn btn-default btn-xs' title='Change theme'><span class='glyphpro glyphpro-embed_close'></span></a></div>");
			//}
		},
		codeMirrorHeight : {
			tool : function(){
				LiveEditor.uiTools.codeMirrorHeight.nav();
				$(document).on('click','.ctrlCodeMirrorHeight a',function(){
					var adjust = false;
					var currentValue = myCodeMirror.cm.options.myH;
					currentValue = parseInt( currentValue.replace("px",'') );
					if($(this).hasClass('increase') && currentValue < (window.innerHeight - LiveEditor.common.cmMinDistFromTop - AppSettings.common.uiDims.workArea.marginB - AppSettings.common.uiDims.workArea.marginT)){
						currentValue = currentValue + LiveEditor.common.uiGridIncr;
						adjust = true;
					} else if($(this).hasClass('decrease') && currentValue > LiveEditor.common.cmMinDistFromBottom) {
						currentValue = currentValue - LiveEditor.common.uiGridIncr;
						adjust = true;
					}
					if(adjust){
						myCodeMirror.cm.options.myH = currentValue+'px';
						myCodeMirror.cm.manageState(myCodeMirror.cm,'w');
						myCodeMirror.cm.manageState(myCodeMirror.cm,'r');
					}
					return false;
				});
			},
			nav : function(){
				$('#ctrlSet4').append("<div class='ctrl ctrlButton ctrlCodeMirrorHeight'><a href='#' class='btn btn-default btn-xs increase' title='Increase height'><span class='glyphicon glyphicon-chevron-up'></span></a></div>");
				$('#ctrlSet4').append("<div class='ctrl ctrlButton ctrlCodeMirrorHeight'><a href='#' class='btn btn-default btn-xs decrease' title='Decrease height'><span class='glyphicon glyphicon-chevron-down'></span></a></div>");
			}
		},
		codeMirrorNewWindow : {
			tool : function(){
				LiveEditor.uiTools.codeMirrorNewWindow.nav();
				$(document).on('click','#ctrlCodeMirrorNewWindow a',function(){
					myCodeMirror.cm.newWindow(myCodeMirror.cm);
					return false;
				});
			},
			nav : function(){
				$('#ctrlSet4').append("<div class='ctrl ctrlButton' id='ctrlCodeMirrorNewWindow'><a href='#' class='btn btn-default btn-xs' title='Move editor to new window'><span class='glyphicon glyphicon-fullscreen'></span></a></div>");
			},
			/*manager : function(){

				var win2;

				function openSecondaryWindow() {
					return win2 = window.open('/josh3736/Wm4nT/show', 'secondary', 'width=300,height=150');
				}

				function flash() {
					$('body').css('background-color', 'red').animate({
						'background-color': '#fff'
					});
				}

				$(function() {

					if (!openSecondaryWindow()) $(document.body).prepend('<a href="#">Popup blocked.  Click here to open the secondary window.</a>').click(function() {
						openSecondaryWindow();
						return false;
					});

					$('#inc').click(function() {
						if (win2) win2.increment();
						else alert('The secondary window is not open.');
						return false;
					});
				});

			}*/
		},
		uiBackgroundFill : {
			tool : function(){
				//LiveEditor.uiTools.uiBackgroundFill.nav();
				$(document).on('click','#ctrlUiBackgroundFill a',function(){
					var jobId = LiveEditor.jobManager.getActiveJobId();
					var fillNo = 0;
					if(LiveEditor.jobManager.jobStates.data[jobId]['backgroundFill'] != null){
						fillNo = LiveEditor.jobManager.jobStates.data[jobId]['backgroundFill'];
					}
					fillLength = Object.keys(LiveEditor.common.uiBackgroundFills).length - 1;
					if(fillNo == fillLength){
						fillNo = -1;
					}
					$.each(LiveEditor.common.uiBackgroundFills, function(i,v){
						if(i <= fillNo){return;}
						LiveEditor.common.uiCssParser(['body','background-image',v]);
						LiveEditor.jobManager.jobStatesManager(jobId,'backgroundFill',i);
						flash.manager('success','Background fill updated',true);
						return false;
					});
					return false;
				});
			},
			//nav : function(){
			//	$('#ctrlSet5').append("<div class='ctrl ctrlButton' id='ctrlUiBackgroundFill'><a href='#' class='btn btn-default btn-xs' title='Background fill'><span class='glyphicon glyphicon-picture'></span></a></div>");
			//},
		},
		uiCmStateManager : function(handle,state){

			var jobId = LiveEditor.jobManager.getActiveJobId();
			var codeMirrorPinStatus = LiveEditor.jobManager.getJobState(jobId,'codeMirrorPin');
			if(codeMirrorPinStatus == 0){
				if(state == 'show'){
					LiveEditor.uiTools.uiCmShow(handle);
				} else if(state == 'hide'){
					LiveEditor.uiTools.uiCmHide(handle);
				}
			}

		},
		uiCmShow : function(handle){
			if(!handle.is(':visible')){
				handle.removeClass('dormant').fadeIn(200);
				$('#cmDeadSpace').fadeOut(200);
				$('#liveEditor' + jobId).removeClass('dormant');


			}
			if(typeof myCodeMirror != "undefined"){
				LiveEditor.uiTools.uiResizerApplyWidth(jobId);
				LiveEditor.uiTools.uiResizerApplyHeight(jobId);

				//Re-scroll to show masked content but only if it is close to the scroll limit
				iframe = $('#liveEditor'+jobId);

				if(typeof myCodeMirror != "undefined"){
					var cmHeight = parseInt( myCodeMirror.cm.options.myH.replace("px",'') );
					var iframeContentHeight = iframe.contents().scrollTop() + (window.innerHeight - cmHeight - LiveEditor.common.iframeBottomOffset - LiveEditor.common.iframeTopOffset - AppSettings.common.uiDims.heights.topBar - AppSettings.common.uiDims.workArea.marginB - AppSettings.common.uiDims.workArea.marginT);
					if(LiveEditor.jobManager.getJobState(jobId,'screenWidthId') == 1){
						iframeContentHeight = iframe.contents().scrollTop() + (window.innerHeight - cmHeight - AppSettings.common.uiDims.heights.topBar - AppSettings.common.uiDims.workArea.marginB - AppSettings.common.uiDims.workArea.marginT);
					}
					if (iframeContentHeight >= iframe.contents().height() - cmHeight) {
						iframe.contents().scrollTop(9999);
					}
				}

			}
		},
		uiCmHide : function(handle){
			if(handle.is(':visible')){
				handle.addClass('dormant').fadeOut(200);
				$('#cmDeadSpace').fadeIn(200);
				$('#liveEditor' + jobId).addClass('dormant');
				//LiveEditor.layoutTools.deviceSimulator.buildScreenWidthStyles();
			}
			if(typeof myCodeMirror != "undefined"){
				LiveEditor.uiTools.uiResizerApplyWidth(jobId);
				LiveEditor.uiTools.uiResizerApplyHeight(jobId);
			}
		},
		uiResizer : function(handle,event){

			handle.css('user-select','none').prop('unselectable','on').on('selectstart',false);

			if(handle.attr('id') == 'sizeHandleCmY'){

				$('body').append('<div id="sizerBlocker" />');

				lastPos = "undefined";
				jobId = LiveEditor.jobManager.getActiveJobId();
				layoutMode = LiveEditor.jobManager.getJobState(jobId,'layoutMode');

				$(document).mousemove(function( event ) {
					//handle.css('background','#'+Math.floor((Math.random() * 9) + 1)+Math.floor((Math.random() * 9) + 1)+Math.floor((Math.random() * 9) + 1)+Math.floor((Math.random() * 9) + 1)+Math.floor((Math.random() * 9) + 1)+Math.floor((Math.random() * 9) + 1));
					//debugCursor(event);



					switch(layoutMode){
						case 0:
							dimWin = window.innerHeight;
							dimPage = event.pageY;
							posVal = dimWin - dimPage;
							break;

						case 1:
							dimWin = window.innerWidth;
							dimPage = event.pageX;
							posVal = dimPage;
							break;
					}

					if(typeof lastPos == "undefined"){
						lastPos = posVal
					}

					var adjust = false;
					switch(layoutMode){
						case 0:
							var currentValue = myCodeMirror.cm.options.myH;
							break;

						case 1:
							var currentValue = myCodeMirror.cm.options.myW;
							break;
					}
					currentValue = parseInt( currentValue.replace("px",'') );

					var pos = posVal + 9;
					if( (pos > lastPos) ){
						currentValue = pos;
						adjust = true;
					} else if( (pos < lastPos) ) {
						currentValue = pos;
						adjust = true;
					}

					if(adjust){

						switch(layoutMode){
							case 0:
								if(LiveEditor.common.iframeLiveResize){
									$('#liveEditor'+jobId).css('bottom',(currentValue + offsetTop + AppSettings.common.uiDims.workArea.marginB)+'px');
									$('#liveEditor'+jobId).css('height', (dimWin - currentValue - offsetBottom - AppSettings.common.uiDims.heights.topBar - offsetTop) + 'px' );
								}
								myCodeMirror.cm.options.myH = currentValue+'px';
								break;

							case 1:
								if(currentValue > LiveEditor.common.cmMinDistFromLeft){
									currentValue -= AppSettings.common.uiDims.workArea.marginL;
									myCodeMirror.cm.options.myW = currentValue+'px';
								}
								break;
						}
						myCodeMirror.cm.manageState(myCodeMirror.cm,'w');
						myCodeMirror.cm.manageState(myCodeMirror.cm,'r');

					}

					lastPos = pos;

					/*-DEBUG-B-*/
					if(debuggery){
						out = '';
						out += 'layoutMode: ' +layoutMode+'<br />';
						out += 'PageDim from WinDim: ' + (dimWin - dimPage) + '<br />';
						out += 'ScreenX: ' + event.screenX + '<br />';
						out += 'ScreenY: ' + event.screenY + '<br />';
						out += 'PageX: ' + event.pageX + '<br />';
						out += 'PageY: ' + dimPage + '<br />';
						out += 'ClientX: ' + event.clientX + '<br />';
						out += 'ClientY: ' + event.clientY + '<br />';
						//out += 'Event:<br />' + event.toSource() + '<br />';
						debug(8,'UI global state',out);
					}
					/*-DEBUG-E-*/
				});

				//Mouseup
				$(document).mouseup(function( event ) {
					switch(layoutMode){
						case 0:
							LiveEditor.uiTools.uiResizerApplyHeight(jobId);
							break;

						case 1:
							LiveEditor.uiTools.uiResizerApplyWidth(jobId);
							break;
					}
					$(document).unbind('mousemove');
					$('#sizerBlocker').remove();
				});

				//if mouse already down, on mouse over rebindd
			}
		},
		uiResizerApplyHeight : function(jobId){

			var layoutMode = LiveEditor.jobManager.getJobState(jobId,'layoutMode');

			var currentValue = myCodeMirror.cm.options.myH;
			currentValue = parseInt( currentValue.replace("px",'') );

			var offsetTop = 0;
			var offsetBottom = 0;
			if(LiveEditor.jobManager.jobStates.data[jobId].screenWidthId > 1){
				offsetTop = LiveEditor.common.iframeTopOffset;
				offsetBottom = LiveEditor.common.iframeBottomOffset;
			}
			var iframe = $('#liveEditor'+jobId);

			switch(layoutMode) {

				case 0:
					if(currentValue > (window.innerHeight - LiveEditor.common.cmMinDistFromTop - offsetBottom - offsetTop - AppSettings.common.uiDims.heights.topBar - AppSettings.common.uiDims.workArea.marginB - AppSettings.common.uiDims.workArea.marginT)){
						currentValue = (window.innerHeight - LiveEditor.common.cmMinDistFromTop - offsetBottom - offsetTop - AppSettings.common.uiDims.heights.topBar - AppSettings.common.uiDims.workArea.marginB - AppSettings.common.uiDims.workArea.marginT);
					}
					if(currentValue < LiveEditor.common.cmMinDistFromBottom){
						currentValue = LiveEditor.common.cmMinDistFromBottom;
					}
					if(currentValue % LiveEditor.common.uiGridIncr != 0){
						currentValue = LiveEditor.common.uiGridIncr * Math.round(currentValue/LiveEditor.common.uiGridIncr);
					}
					var cmH = currentValue;
					if(iframe.hasClass('dormant')){cmH = offsetBottom};
					iframe.css('bottom',(cmH + offsetTop + AppSettings.common.uiDims.workArea.marginB)+'px');
					iframe.css('height', (window.innerHeight - cmH - offsetBottom - offsetTop - AppSettings.common.uiDims.heights.topBar - AppSettings.common.uiDims.workArea.marginB - AppSettings.common.uiDims.workArea.marginT) + 'px' );

					break;

				case 1:
					var iframe = $('#liveEditor'+jobId);
					iframe.css('bottom',(offsetTop + AppSettings.common.uiDims.workArea.marginB)+'px');
					iframe.css('height', (window.innerHeight - offsetBottom - offsetTop - AppSettings.common.uiDims.heights.topBar - AppSettings.common.uiDims.workArea.marginB - AppSettings.common.uiDims.workArea.marginT) + 'px' );

					break;

			}

			myCodeMirror.cm.options.myH = currentValue+'px';
			myCodeMirror.cm.manageState(myCodeMirror.cm,'w');
			myCodeMirror.cm.manageState(myCodeMirror.cm,'r');

			LiveEditor.layoutTools.baselineGrid.apply( jobId, LiveEditor.jobManager.getJobState(jobId,'baselineGridX'), 'x', true );
			LiveEditor.layoutTools.baselineGrid.apply( jobId, LiveEditor.jobManager.getJobState(jobId,'baselineGridY'), 'y', true );

		},
		uiResizerApplyWidth : function(jobId){

			var layoutMode = LiveEditor.jobManager.getJobState(jobId,'layoutMode');
			var screenWidthId = LiveEditor.jobManager.getJobState(jobId,'screenWidthId');

			var currentValue = myCodeMirror.cm.options.layoutModes[layoutMode].myW;
			currentValue = parseInt( currentValue.replace("px",'') );

			switch(layoutMode) {

				case 0:
					currentValue = window.innerWidth - AppSettings.common.uiDims.workArea.marginL - AppSettings.common.uiDims.workArea.marginR;

					var activeAreaL = AppSettings.common.uiDims.workArea.marginL;
					var activeAreaW = window.innerWidth - activeAreaL - AppSettings.common.uiDims.workArea.marginR;
					var iframe = $('#liveEditor'+jobId);
					if(screenWidthId > 1){
						var screenWidth = parseInt( LiveEditor.common.screenWidths[screenWidthId].width.replace("px",'') );
						iframe.css('left', (activeAreaL + ((activeAreaW - screenWidth)/2)) + 'px');
						iframe.css('width', screenWidth + 'px');
					} else {
						iframe.css('left', activeAreaL + 'px');
						iframe.css('width', activeAreaW + 'px');
					}

				    break;

				case 1:
					if(currentValue > (window.innerWidth - LiveEditor.common.cmMinDistFromRight - AppSettings.common.uiDims.workArea.marginL - AppSettings.common.uiDims.workArea.marginR)){
						currentValue = (window.innerWidth - LiveEditor.common.cmMinDistFromRight - AppSettings.common.uiDims.workArea.marginL - AppSettings.common.uiDims.workArea.marginR);
					}
					if(currentValue < LiveEditor.common.cmMinDistFromLeft){
						currentValue = LiveEditor.common.cmMinDistFromLeft;
					}
					if(currentValue % LiveEditor.common.uiGridIncr != 0){
						currentValue = LiveEditor.common.uiGridIncr * Math.round(currentValue/LiveEditor.common.uiGridIncr);
					}

					var iframe = $('#liveEditor'+jobId);
					var cmW = currentValue;
					if(iframe.hasClass('dormant')){cmW = 0};
					var activeAreaL = cmW + AppSettings.common.uiDims.workArea.marginL;
					var activeAreaW = window.innerWidth - activeAreaL - AppSettings.common.uiDims.workArea.marginR;
					if(screenWidthId > 1){
						var screenWidth = parseInt( LiveEditor.common.screenWidths[screenWidthId].width.replace("px",'') );
						iframe.css('left', (activeAreaL + ((activeAreaW - screenWidth)/2)) + 'px');
						iframe.css('width', screenWidth + 'px');
					} else {
						iframe.css('left', activeAreaL + 'px');
						iframe.css('width', activeAreaW + 'px');
						$('#editor').css('margin-left', AppSettings.common.uiDims.workArea.marginL + cmW + 'px');
					}

			        break;

			}

			$('.CodeMirror').css('left', AppSettings.common.uiDims.workArea.marginL + 'px');

			myCodeMirror.cm.options.myW = currentValue+'px';
			myCodeMirror.cm.manageState(myCodeMirror.cm,'w');
			myCodeMirror.cm.manageState(myCodeMirror.cm,'r');

			LiveEditor.layoutTools.baselineGrid.apply( jobId, LiveEditor.jobManager.getJobState(jobId,'baselineGridX'), 'x', true );
			LiveEditor.layoutTools.baselineGrid.apply( jobId, LiveEditor.jobManager.getJobState(jobId,'baselineGridY'), 'y', true );

			//$('#cmDeadSpace');

		},
	};
	LiveEditor.setWorkArea = function(settings){
		/*
		e.g.
		settings = {marginT:100}
		LiveEditor.setWorkArea({marginT:100});
		LiveEditor.setWorkArea({marginT:null}); //reset to last stored value
		*/

		if(typeof settings != "undefined"){
			for(i in settings){
				if(settings[i] != AppSettings.common.uiDims.workArea[i] && settings[i] != null){
					$.cookie('workArea.'+i+'.last',AppSettings.common.uiDims.workArea[i]);
					AppSettings.common.uiDims.workArea[i] = parseInt(settings[i]);
				} else if(settings[i] == null && $.cookie('workArea.'+i+'.last') != null){
					AppSettings.common.uiDims.workArea[i] = parseInt($.cookie('workArea.'+i+'.last'));
				}
			}
		}
		$('#editor').css('margin-top',AppSettings.common.uiDims.workArea.marginT+'px');
		$('#editor').css('margin-right',AppSettings.common.uiDims.workArea.marginR+'px');
		$('#editor').css('margin-bottom',AppSettings.common.uiDims.workArea.marginB+'px');
		$('#editor').css('margin-left',AppSettings.common.uiDims.workArea.marginL+'px');

		var jobId = LiveEditor.jobManager.getActiveJobId();
		LiveEditor.uiTools.uiResizerApplyWidth(jobId);
		LiveEditor.uiTools.uiResizerApplyHeight(jobId);



		//LiveEditor.layoutTools.deviceSimulator.buildScreenWidthStyles();

	};
	LiveEditor.jobTools = {
		jobList : {
			tool : function(){
				LiveEditor.jobTools.jobList.nav();
			},
			nav : function(){
				var content = '<div id="jobListNav"></div>';
				var dataJson = '{"title":"Job manager","content":'+JSON.stringify(content)+'}';
				$('#ctrlSet0').append("<div class='ctrl ctrlButton edgeBarOpener edgeBarL' id='ctrlJobList'><a href='#' class='btn btn-default btn-xs' data-json='"+dataJson+"' title='Job manager'><span class='glyphicon glyphicon-home'></span></a></div>");
			},
			applyContentJobListNav : function(){
				if($('#jobListNav').length != 0){
					var content = '';
					content += '<ul id="navJobs" class="myNav">';
					content += '<li><a href="jobs/index">Job manager</a></li>';
					for(i in LiveEditor.jobManager.jobs){
						var jobId = LiveEditor.jobManager.jobs[i].id;
						content += '<li data-job="'+jobId+'" id="navJob'+jobId+'" class="jobListJob jobStatus'+LiveEditor.jobManager.jobStatuses.data[i]+'">';
						content += '<a href="#" class="navJob">'+LiveEditor.jobManager.jobs[i].name+'</a>';
						if(LiveEditor.jobManager.jobStatuses.data[i] > 0){
							content += '<a href="#" class="navJobClose navJobCtrl" title="Close job">';
								content += '<span class="glyphicon glyphicon-remove"></span>';
							content += '</a>';
						}
						var htmlClass = '';
						if(LiveEditor.jobManager.getJobState(jobId,'cached') == 1 && LiveEditor.jobManager.jobStatuses.data[i] == 2){
							htmlClass = 'active';
						}
						//content += '<a href="#" class="navJobCached navJobCtrl '+htmlClass+'" title="Toggle cache mode">';
						//content += '<span class="glyphicon glyphicon-camera"></span>';
						//content += '</a>';
						content += '</li>';
					}
					content += '</ul>';
					$('#jobListNav').html(content);
				}
			}
		},
		resetAll : {
			tool : function(){
				//LiveEditor.jobTools.resetAll.nav();
				$(document).on('click','#ctrlResetAll a',function(){
					if(confirm('This will reset to the default state. Are you sure?')){
						LiveEditor.jobManager.jobStatuses = new LiveEditor.jobManager.jobStatusesConstructor('reset');
						LiveEditor.jobManager.jobStates = new LiveEditor.jobManager.jobStatesConstructor('reset');
						LiveEditor.common.uiState = new LiveEditor.common.uiStateConstructor('reset');
						//alert($.cookie().toSource());
						window.location.reload();
						//LiveEditor.jobManager.initJobs();
					}
					return false;
				});
			},
			nav : function(){
				//$('#ctrlSet1').append("<div class='ctrl ctrlButton' id='ctrlResetAll'><a href='#' class='btn btn-default btn-xs' title='Reset'><span class='glyphicon glyphicon-off'></span></a></div>");
			}
		},
		saveDataState : {
			tool : function(){
				if(!LiveEditor.common.autoPersistDataState){
					LiveEditor.jobTools.saveDataState.nav();
					$(document).on('click','#ctrlSaveDataState a',function(){
						save_data_state();
						return false;
					});
				}
			},
			nav : function(){
				$('#ctrlSet1').append("<div class='ctrl ctrlButton' id='ctrlSaveDataState'><a href='#' class='btn btn-default btn-xs' title='Save current application  state'><span class='glyphicon glyphicon-save'></a></a></div>");
			}
		},
		backupManager : {
			tool : function(){
				LiveEditor.jobTools.backupManager.nav();
				$(document).on('click','.restoreBackup',function(){
					if(confirm("Publishing stored code may overwrite someone elses changes\n\nAre you sure you want to load stored code?")){
						var backupKey = $(this).attr('data-key');
						css_backup_manager( LiveEditor.jobManager.getActiveJobId(), 'r', backupKey );
					}
					return false;
				});
				$(document).on('click','.createBackup',function(){
					css_backup_manager( LiveEditor.jobManager.getActiveJobId(), 'w' );
					return false;
				});
				//$(document).on('click','#backupManager A',function(){
					//var val = $(this).text();
					//LiveEditor.layoutTools.inspector.transferContent(val,'around');
					//return false;
				//});
			},
			nav : function(){
				var dataJson = '{"title":"","content":"","w":600}';
				$('#ctrlSet1').append("<div class='ctrl ctrlButton edgeBarOpener edgeBarL' id='ctrlBackupManager'><a href='#' class='btn btn-default btn-xs' data-json='"+dataJson+"' title='Backup Manager'><span class='glyphicon glyphicon-hdd'></span></a></div>");
			}
		},
	};
	LiveEditor.appTools = {

		uiSettings : {
			tool : function(){
				LiveEditor.appTools.uiSettings.nav();
			},
			nav : function(){
				//var jobId = LiveEditor.jobManager.getActiveJobId();
				var content = '<li><h4>Global settings</h4></li>';
				content += '<li>';
					content += '<ul>';
						content += '<li><label>Editor font size</label><div class="ctrl ctrlButton ctrlCodeMirrorFontSize"><a href="#" class="btn btn-default btn-xs decrease" title="Decrease font size"><span class="glyphpro glyphpro-text_smaller"></span></a><a href="#" class="btn btn-default btn-xs increase" title="Increase font size"><span class="glyphpro glyphpro-text_bigger"></span></a></div></li>';
						content += '<li><div class="ctrl ctrlButton" id="ctrlCodeMirrorTheme"><a href="#" class="btn btn-default btn-xs" title="Change theme"><span class="glyphpro glyphpro-embed_close"></span></a><label>Editor theme</label></div></li>';
						content += '<li><div class="ctrl ctrlButton" id="ctrlResetAll"><a href="#" class="btn btn-default btn-xs" title="Reset"><span class="glyphicon glyphicon-off"></span></a><label>Reset to default</label></div></li>';
					content += '</ul>';
				content += '</li>';
				content += '<li><h4>Job settings</h4>';//LiveEditor.jobManager.jobs[jobId].name
					content += '<ul>';
						content += '<li><label>Baseline grid size</label><div class="ctrl ctrlButton ctrlUiBaselineGridSize"><a href="#" class="btn btn-default btn-xs decrease" title="Decrease grid size"><span class="glyphicon glyphicon-minus"></span></a><a href="#" class="btn btn-default btn-xs increase" title="Increase grid size"><span class="glyphicon glyphicon-plus"></span></a></div></li>';
						content += '<li><div class="ctrl ctrlInput baselineGridColor"><label>Primary grid colour</label><input value="'+LiveEditor.common.baselineGridColor1+'" class="baselineGridColor1 color" /></div></li>';
						content += '<li><div class="ctrl ctrlInput baselineGridColor"><label>Secondary grid colour</label><input value="'+LiveEditor.common.baselineGridColor2+'" class="baselineGridColor2 color" /></div></li>';
						for(i in LiveEditor.common.baselineGridColorPresets){
							var c1 = LiveEditor.common.baselineGridColorPresets[i][0];
							var c2 = LiveEditor.common.baselineGridColorPresets[i][1];
							content += '<li><a class="gridPaletteName">'+i+'</a> <span class="gridPaletteColors"><c class="c1" style="background-color:'+c1+'" data-color="'+c1+'">&nbsp;</c> <c class="c2" style="background-color:'+c2+'" data-color="'+c2+'">&nbsp;</c></span></li>';
						}
						
						content += '<li><div class="ctrl ctrlButton" id="ctrlUiBackgroundFill"><a href="#" class="btn btn-default btn-xs" title="Background fill"><span class="glyphicon glyphicon-picture"></span></a><label>Work area background</label></div></li>';
					content += '</ul>';
				content += '</li>';
				//var content = $('#edgeBarContent_uiSettings').clone( true ).html();
				var dataJson = '{"title":"My settings","content":'+JSON.stringify(content)+',"w":300}';
				$('#ctrlSet6').append("<div class='ctrl ctrlButton edgeBarOpener edgeBarL' id='ctrlUiSettings'><a href='#' class='btn btn-default btn-xs' data-json='"+dataJson+"' title='Settings'><span class='glyphicon glyphicon-cog'></span></a></div>");
			},
		},
		help : {
			tool : function(){
				LiveEditor.appTools.help.nav();
			},
			nav : function(){
				var dataJson = '{"title":"Help","content":{"url":"'+APP_BASE_URI+'live_editors/help"},"w":600}';
				$('#ctrlSet6').append("<div class='ctrl ctrlButton edgeBarOpener edgeBarL' id='ctrlHelp'><a href='#' class='btn btn-default btn-xs' data-json='"+dataJson+"' title='Help'><span class='glyphicon glyphicon-question-sign'></span></a></div>");
			}
		}
	}
	
	LiveEditor.jobManager = {};
	LiveEditor.jobManager.jobs = appData.jobs;
	//LiveEditor.jobManager.activeData = function(){
		//pad out the jobs object with additional data used by the editor
		for(i in LiveEditor.jobManager.jobs){
			if(LiveEditor.jobManager.jobs[i].activeData == null){
				LiveEditor.jobManager.jobs[i].activeData = {};
			}
			LiveEditor.jobManager.jobs[i].activeData.urlVariants = uri_variants(LiveEditor.jobManager.jobs[i].url);
		}

	//};

	LiveEditor.jobManager.jobStatusesConstructor = function(mode){

		this.name = 'LiveEditor.jobManager.jobStatuses';
		this.data = {};

		var jobStatuses = { "data" : {} };

		if(typeof mode != "undefined"){
			if(mode == 'reset'){
				persist_data(this,'d');
			}
		} else {

			if($.cookie('LiveEditor.jobManager.jobStatuses') != null){
				jobStatuses = JSON.parse($.cookie('LiveEditor.jobManager.jobStatuses'));
			} else if(persist_data(this,'r')){
				jobStatuses = JSON.parse($.cookie('LiveEditor.jobManager.jobStatuses'));
			}

		}

		for(i in LiveEditor.jobManager.jobs){
			if(typeof jobStatuses.data[i] != "undefined"){
				this.data[i] = jobStatuses.data[i];
			} else {
				this.data[i] = {};
			}
		}
	};

	LiveEditor.jobManager.jobStatesConstructor = function(mode){

		this.name = 'LiveEditor.jobManager.jobStates';
		this.data = {};

		var jobStates = { "data" : {} };

		if(typeof mode != "undefined"){
			if(mode == 'reset'){
				persist_data(this,'d');
			}
		} else {

			if($.cookie('LiveEditor.jobManager.jobStates') != null){
				jobStates = JSON.parse($.cookie('LiveEditor.jobManager.jobStates'));
			} else if(persist_data(this,'r')){
				jobStates = JSON.parse($.cookie('LiveEditor.jobManager.jobStates'));
			}

		}

		for(i in LiveEditor.jobManager.jobs){
			if(typeof jobStates.data[i] != "undefined"){
				this.data[i] = jobStates.data[i];
			} else {
				this.data[i] = {};
			}
		}


		/*
		//DUMMY
		this.data = {
			1 : {
				'default_uri' : APP_BASE_URI+'test_site_1b.htm',
				'history' : {[
					0 : 'guardian.co.uk',
					1 : 'newscientist.co.uk'
				]}
			},
			2 : {
				'screenWidthId' : 2,
			},
			3 : {
				'screenWidthId' : 3,
			}
		};*/
	};

	LiveEditor.common.uiStateConstructor = function(mode){

		this.name = 'LiveEditor.common.uiState';
		this.data = {};

		if(typeof mode != "undefined"){
			if(mode == 'reset'){
				persist_data(this,'d');
			}
		} else {

			if($.cookie('LiveEditor.common.uiState') != null){
				LiveEditor.common.uiState = JSON.parse($.cookie('LiveEditor.common.uiState'));
			} else if(persist_data(this,'r')){
				LiveEditor.common.uiState = JSON.parse($.cookie('LiveEditor.common.uiState'));
			} else {
				LiveEditor.common.uiState = LiveEditor.common.uiState;
			}

		}
		this.data = LiveEditor.common.uiState.data;

	};

	LiveEditor.jobManager.jobStatuses = new LiveEditor.jobManager.jobStatusesConstructor();
	LiveEditor.jobManager.jobStates = new LiveEditor.jobManager.jobStatesConstructor();
	LiveEditor.common.uiState = new LiveEditor.common.uiStateConstructor();

	LiveEditor.jobManager.initJobs = function(){
		//IAH this should be moved to where the object is created maybe??
		if($.cookie('LiveEditor.jobManager.jobStatuses') == null){

			if(persist_data(LiveEditor.jobManager.jobStatuses,'r')){
				LiveEditor.jobManager.jobStatuses = JSON.parse($.cookie('LiveEditor.jobManager.jobStatuses'));
				var activeJob = false;
				$.each(LiveEditor.jobManager.jobStatuses.data, function(jobId,jobStatus){
					if(jobStatus == 1){
						LiveEditor.jobManager.jobStatusManager(jobId,0);
					}
					if(jobStatus == 2){
						activeJob = true;
						LiveEditor.jobManager.jobStatusManager(jobId,2);
					}
				});
				if(!activeJob){
					LiveEditor.jobManager.resetStatuses();
				}
			} else {

				var count = 0;
				$.each(LiveEditor.jobManager.jobs, function(i,job){
					if(count == 0){
						//LiveEditor.jobManager.jobStatuses[ job['id'] ] = 2;
						LiveEditor.jobManager.jobStatusManager(job['id'],2);
					} else {
						//LiveEditor.jobManager.jobStatuses[ job['id'] ] = 0;
						LiveEditor.jobManager.jobStatusManager(job['id'],0);
					}
					count++;
				});
			}

			//$.cookie('LiveEditor.jobManager.jobStatuses',JSON.stringify(LiveEditor.jobManager.jobStatuses));
		} else {

			LiveEditor.jobManager.jobStatuses = JSON.parse($.cookie('LiveEditor.jobManager.jobStatuses'));
			var activeJob = false;
			$.each(LiveEditor.jobManager.jobStatuses.data, function(jobId,jobStatus){
				if(jobStatus == 1){
					LiveEditor.jobManager.jobStatusManager(jobId,0);
				}
				if(jobStatus == 2){
					activeJob = true;
					LiveEditor.jobManager.jobStatusManager(jobId,2);
				}
			});
			if(!activeJob){
				LiveEditor.jobManager.resetStatuses();
			}
		}

		LiveEditor.jobManager.jobStatesManager();
		LiveEditor.jobManager.restoreJobStates();
		//LiveEditor.jobManager.restoreActiveJobUIState();
		//ui_update();

		/*-DEBUG-B-*/
		if(debuggery){
			$('.db0').remove();
			out = '';
			var jobs = LiveEditor.jobManager.jobs;
			$.each(jobs, function(i,v){
				out += 'Job ' + i + "<br />";
				$.each(v,function(ii,vv){
					if (vv instanceof Object) {
						out += '\\_____ ' + ii +"<br />";
						$.each(vv,function(iii,vvv){
							out += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\\_____ ' + iii +' : ' + vvv +"<br />";
						});
					} else {
						out += '\\_____ ' + ii +' : '+vv+"<br />";
					}
				});
				out += "<br />";
			});
			debug(0,'Jobs',out);
		}
		/*-DEBUG-E-*/
	};
	//Job status
	LiveEditor.jobManager.resetStatuses = function(){
		LiveEditor.jobManager.jobStatuses = new LiveEditor.jobManager.jobStatusesConstructor('reset');
		LiveEditor.jobManager.initJobs();
	};
	LiveEditor.jobManager.activateJob = function(job){

		$.each(LiveEditor.jobManager.jobStatuses.data, function(jobId,jobStatus){
			if(jobStatus == 2){
				LiveEditor.jobManager.jobStatusManager(jobId,1);
			}
			if(jobId == job.id && jobStatus == 1){
				//if the job to be focused is already dormant then resbuild the codeMirror instance
				myCodeMirror = new LiveEditor.codeMirror.init( job.id );
			}
		});
		LiveEditor.jobManager.jobStatusManager(job.id,2);

		/*if(LiveEditor.jobManager.jobStates.data[job.id]['screenWidthId'] != null){
			var screenWidthId = LiveEditor.jobManager.jobStates.data[job.id]['screenWidthId'];
			LiveEditor.layoutTools.deviceSimulator.applyScreenWidth(screenWidthId,job.id);
		} else {
			LiveEditor.layoutTools.deviceSimulator.applyScreenWidth(1,job.id);
		}*/
	};
	LiveEditor.jobManager.jobStatusManager = function(jobId,jobStatus){
		//0: off
		//1: on
		//2: on, focused

		if(LiveEditor.jobManager.jobStatuses.data[jobId] == 2 && jobStatus == 0){
			var activeJob = false;
			//focused editor is being closed so focus the next available editor
			$.each(LiveEditor.jobManager.jobStatuses.data, function(id,status){
				if(id != jobId && status == 1){
					activeJob = true;
					LiveEditor.jobManager.jobStatusManager(id,2);
					myCodeMirror = new LiveEditor.codeMirror.init( id );
					//alert(id);
					return false;
				}
			});
			if(!activeJob){
				LiveEditor.jobManager.resetStatuses();
			} else {
				LiveEditor.jobManager.jobStatuses.data[jobId] = jobStatus;
				$.cookie('LiveEditor.jobManager.jobStatuses',JSON.stringify(LiveEditor.jobManager.jobStatuses));
				if(LiveEditor.common.autoPersistDataState){
					persist_data(LiveEditor.jobManager.jobStatuses);
				}
			}
		} else {
			LiveEditor.jobManager.jobStatuses.data[jobId] = jobStatus;
			$.cookie('LiveEditor.jobManager.jobStatuses',JSON.stringify(LiveEditor.jobManager.jobStatuses));
			if(LiveEditor.common.autoPersistDataState){
				persist_data(LiveEditor.jobManager.jobStatuses);
			}
		}

		if(jobStatus == 0){
			$('#liveEditor'+jobId).remove();
		}

		LiveEditor.jobManager.iframeManager();
		LiveEditor.jobManager.restoreActiveJobUIState();

		//ui_update();

		/*-DEBUG-B-*/
		if(debuggery){
			$('.db1').remove();
			out = '';
			if($.cookie('LiveEditor.jobManager.jobStatuses') != null){
				var jobStatuses = JSON.parse($.cookie('LiveEditor.jobManager.jobStatuses'));
				$.each(jobStatuses.data, function(i,v){
					out += 'Job id ' + i +' : '+v+"<br />"
				});
			}
			debug(1,'Job Status',out);
		}
		/*-DEBUG-E-*/
	};
	LiveEditor.jobManager.getActiveJobId = function(){
		if(LiveEditor.common.activeJobId == false){
			for(jobId in LiveEditor.jobManager.jobStatuses.data){
				if(LiveEditor.jobManager.jobStatuses.data[jobId] == 2){
					LiveEditor.common.activeJobId = jobId;
					return jobId;
				}
			}
			return false;
		} else {
			return LiveEditor.common.activeJobId;
		}
	};

	//Job state
	LiveEditor.jobManager.jobStatesManager = function(jobId,key,value,nestedKey){

		var invalid = false;
		if(value == "invalid"){
			invalid = true;
			value = null;
		}

		if(key == 'history' && nestedKey != "undefined"){

			//remove history item

			LiveEditor.jobManager.jobStates.data[jobId]['history'].splice(nestedKey, 1);
			$.cookie('LiveEditor.jobManager.jobStates',JSON.stringify(LiveEditor.jobManager.jobStates));
			if(LiveEditor.common.autoPersistDataState){
				persist_data(LiveEditor.jobManager.jobStates);
			}

		} else {

			if(typeof jobId != "undefined"){
				//set object and refresh cookie
				//alert(key + ' --> ' + value);

				LiveEditor.jobManager.jobStates.data[jobId][key] = value;
				$.cookie('LiveEditor.jobManager.jobStates',JSON.stringify(LiveEditor.jobManager.jobStates));
				if(LiveEditor.common.autoPersistDataState){
					persist_data(LiveEditor.jobManager.jobStates);
				}

			}
			if(key == 'default_uri'){

					if(LiveEditor.jobManager.jobStates.data[jobId]['history'] == null){
						LiveEditor.jobManager.jobStates.data[jobId]['history'] = [];
					}
					if($.inArray(value,LiveEditor.jobManager.jobStates.data[jobId]['history']) == -1){

						if(!invalid){
							LiveEditor.jobManager.jobStates.data[jobId]['history'].push(value);
						} else {
							//remove last history item as it was invalid
							LiveEditor.jobManager.jobStates.data[jobId]['history'].pop();
						}

						$.cookie('LiveEditor.jobManager.jobStates',JSON.stringify(LiveEditor.jobManager.jobStates));
						if(LiveEditor.common.autoPersistDataState){
							persist_data(LiveEditor.jobManager.jobStates);
						}
					}

			}

		}
		/* else if($.cookie('LiveEditor.jobManager.jobStates') != null) {
			//get cookie data into object
			alert('get');
			LiveEditor.jobManager.jobStates = JSON.parse($.cookie('LiveEditor.jobManager.jobStates'));
			////persist_data(LiveEditor.jobManager.jobStates,'r');
		} else {
			//set cookie only
			$.cookie('LiveEditor.jobManager.jobStates',JSON.stringify(LiveEditor.jobManager.jobStates));
			////persist_data(LiveEditor.jobManager.jobStates);
		}*/



		/*-DEBUG-B-*/
		if(debuggery){
			$('.db2').remove();
			out = '';
			if($.cookie('LiveEditor.jobManager.jobStates') != null){
				var jobStates = JSON.parse($.cookie('LiveEditor.jobManager.jobStates'));
				$.each(jobStates.data, function(i,v){
					var out2 = '<br />';
					$.each(v,function(ii,vv){
						if (vv instanceof Object) {
							vv = vv.toSource();
						}
						out2 += '\\_____  ' + ii +' : '+vv+"<br />"
					});
					out += 'Job id ' + i +' : '+out2+"<br />"
				});
			}
			debug(2,'Jobs States',out);
		}
		/*-DEBUG-E-*/

	};
	LiveEditor.jobManager.getJobState = function(jobId,key){
		if(LiveEditor.jobManager.jobStates.data[jobId][key] == null){
			if(typeof LiveEditor.common.jobStateDefaults[key] != "undefined"){
				return LiveEditor.common.jobStateDefaults[key];
			}
			return 0;
		}
		return LiveEditor.jobManager.jobStates.data[jobId][key];
	};
	LiveEditor.jobManager.restoreJobStates = function(){
		$.each(LiveEditor.jobManager.jobStates.data,function(jobId,jobStates){
			$.each(jobStates,function(jobStateKey,jobStateValue){
				if(jobStateKey == 'screenWidthId'){
					LiveEditor.layoutTools.deviceSimulator.applyScreenWidth(jobStateValue,jobId);
				}
			});
		});
	};
	LiveEditor.jobManager.restoreActiveJobUIState = function(item){

		if(typeof item == "undefined"){item = null}

		var activeJobId = LiveEditor.jobManager.getActiveJobId();

		if(item == null){

			//general
			$.each(LiveEditor.jobManager.jobStatuses.data, function(jobId,jobStatus){
				var iframe = $('#liveEditor'+jobId);
				iframe.removeClass( function(index,css){ return(css.match (/(^|\s)jobStatus\S+/g) || []).join(' '); } );
				iframe.addClass('jobStatus'+jobStatus);

				var navJob = $('#navJob'+jobId);
				navJob.removeClass( function(index,css){ return(css.match (/(^|\s)jobStatus\S+/g) || []).join(' '); } );
				navJob.addClass('jobStatus'+jobStatus);
			});

		}

		if(item == null){
			//Device simulator
			if(activeJobId){
				if(LiveEditor.jobManager.jobStates.data[activeJobId]['screenWidthId'] != null){
					var screenWidthId = LiveEditor.jobManager.jobStates.data[activeJobId]['screenWidthId'];
					LiveEditor.layoutTools.deviceSimulator.applyScreenWidth(screenWidthId,activeJobId);
				} else {
					LiveEditor.layoutTools.deviceSimulator.applyScreenWidth(1,activeJobId);
				}
			}
		}

		if(item == null || item == 'colorPicker'){
			//Color Picker
			if(activeJobId){
				if(LiveEditor.jobManager.jobStates.data[activeJobId]['colorPicker'] != null){
					var status = LiveEditor.jobManager.jobStates.data[activeJobId]['colorPicker']['status'];
					LiveEditor.layoutTools.colorPicker.colorPickerManager(activeJobId,status);
				} else {
					LiveEditor.layoutTools.colorPicker.colorPickerManager(activeJobId,0);
				}
			}
		}

		if(item == null){
			//Inspector
			if(activeJobId){
				if(LiveEditor.jobManager.jobStates.data[activeJobId]['inspector'] != null){
					var status = LiveEditor.jobManager.jobStates.data[activeJobId]['inspector']['status'];
					LiveEditor.layoutTools.inspector.inspectorManager(activeJobId,status);
					$('BODY .elInf2').empty().remove();
				} else {
					LiveEditor.layoutTools.inspector.inspectorManager(activeJobId,0);
					$('BODY .elInf2').empty().remove();
				}
			}
		}

		if(item == null){
			//Background fill
			if(activeJobId){
				if(LiveEditor.jobManager.jobStates.data[activeJobId]['backgroundFill'] != null){
					var fillno = LiveEditor.jobManager.jobStates.data[activeJobId]['backgroundFill'];
					LiveEditor.common.uiCssParser([ 'body','background-image',LiveEditor.common.uiBackgroundFills[fillno] ]);
				} else {
					LiveEditor.common.uiCssParser([ 'body','background-image',LiveEditor.common.uiBackgroundFills[ LiveEditor.common.uiBackgroundFillDefault ] ]);
				}
			}
		}

		if(item == null || item == 'codeMirrorPin'){
			//CodeMirrorPin
			if(activeJobId){
				var codeMirrorPinStatus = LiveEditor.jobManager.getJobState(activeJobId,'codeMirrorPin');
				if (codeMirrorPinStatus == 0){
					////LiveEditor.uiTools.uiCmHide($('.CodeMirror'));
					$('#ctrlCodeMirrorPin a').removeClass('active');
				} else if (codeMirrorPinStatus == 1){
					////LiveEditor.uiTools.uiCmShow($('.CodeMirror'));
					$('#ctrlCodeMirrorPin a').addClass('active');
				}
			}
		}

		if(item == null || item == 'layoutMode'){
			//layoutMode
			if(activeJobId){
				var state = LiveEditor.jobManager.getJobState(activeJobId,'layoutMode');
				$('body').removeClass('layoutMode0');
				$('body').removeClass('layoutMode1');
				$('body').addClass('layoutMode'+state);
				if (state == 0){
					$('#ctrlLayoutMode a').removeClass('active');
				} else if (state == 1){
					$('#ctrlLayoutMode a').addClass('active');
				}
			}
		}

		if(item == null || item == 'baselineGridX'){
			//baselineGridX
			if(activeJobId){
				var state = LiveEditor.jobManager.getJobState(activeJobId,'baselineGridX');
				LiveEditor.layoutTools.baselineGrid.apply(jobId,state,'x');
			}
		}

		if(item == null || item == 'baselineGridY'){
			//baselineGridY
			if(activeJobId){
				var state = LiveEditor.jobManager.getJobState(activeJobId,'baselineGridY');
				LiveEditor.layoutTools.baselineGrid.apply(jobId,state,'y');
			}
		}

		if(item == null || item == 'cached'){
			//cached site
			if(activeJobId){
				var state = LiveEditor.jobManager.getJobState(activeJobId,'cached');
				LiveEditor.remoteTools.useCached.apply(jobId,state);
			}
		}


	}
	//Iframes
	LiveEditor.jobManager.iframeManager = function(){

		if(appData.mode == 1){
		
			$.each(LiveEditor.jobManager.jobStatuses.data, function(jobId,jobStatus) {

				if(jobStatus > 0){
					//$('#liveEditor'+jobId).remove();
					if($('#liveEditor'+jobId).length == 0){
						//alert($('#liveEditor'+jobId).length);
						//alert(LiveEditor.jobManager.jobStatuses.data.toSource());
						LiveEditor.iframe.createIframe(LiveEditor.jobManager.jobs[jobId]);
					}
					//LiveEditor.jobManager.jobStatusManager(jobId,jobStatus);
				} else {
					$('#liveEditor'+jobId).remove();
				}
			});
			
		}
	};




	//Activated page functions
	//This is thi part which activates the code on the page so it becomes ELECTRIFIED!
	LiveEditor.activationManager = {};
	LiveEditor.activationManager.editors = {};

	LiveEditor.activationManager.init = function(){

		if(appData.mode == 1){

			var activeJobId = LiveEditor.jobManager.getActiveJobId();

			//make this a editor destroy function. it removes any textareas and editor css from closed jobs
			for(i in LiveEditor.jobManager.jobs){
				if(LiveEditor.jobManager.jobStatuses.data[i] == 0){
					if(typeof LiveEditor.activationManager.editors[i] != "undefined"){
						if(typeof LiveEditor.activationManager.editors[i].textarea != "undefined"){
							LiveEditor.activationManager.editors[i].textarea.remove();
						}
						delete LiveEditor.activationManager.editors[i];
					}
				}
			}

			for(i in LiveEditor.activationManager.editors){
				LiveEditor.activationManager.editors[i].textarea.hide();
			}

			$('#loadingBlock').show();
			//$('#liveEditor'+activeJobId).hide();
			$('#liveEditor'+activeJobId).unbind('load');
			$('#liveEditor'+activeJobId).load(function() {

						//$('#edgeBarInner').append('id:'+activeJobId+'<br />');
						//deactivation function
						if(typeof LiveEditor.activationManager.editors[activeJobId] != "undefined"){
							if(typeof LiveEditor.activationManager.editors[activeJobId].textarea != "undefined"){
								LiveEditor.activationManager.editors[activeJobId].textarea.remove();
							}
						}

						if(typeof LiveEditor.activationManager.editors[activeJobId] == "undefined"){
							LiveEditor.activationManager.editors[activeJobId] = {};
						}

						/*if(typeof LiveEditor.activationManager.editors[activeJobId].activationStatus == "undefined"){
							LiveEditor.activationManager.editors[activeJobId].activationStatus = 0;
						}*/

						LiveEditor.activationManager.activatePage(activeJobId);
						/*-DEBUG-B-*/
						if(debuggery){
							LiveEditor.activationManager.debug(activeJobId);
						}
						/*-DEBUG-E-*/
						LiveEditor.activationManager.editors[activeJobId].textarea.show();

						myCodeMirror = new LiveEditor.codeMirror.init(activeJobId);
						LiveEditor.uiTools.uiResizerApplyHeight(activeJobId);
						LiveEditor.uiTools.uiResizerApplyWidth(activeJobId);

						//Activate additional items based on job state
						if(LiveEditor.jobManager.jobStates.data[activeJobId].inspector != null){
							LiveEditor.layoutTools.inspector.inspectorManager(activeJobId,0);
							LiveEditor.layoutTools.inspector.inspectorManager(activeJobId,LiveEditor.jobManager.jobStates.data[activeJobId].inspector.status);
						}

						$(document).on('mousedown','.sizeHandle',function(event){
							LiveEditor.uiTools.uiResizer($(this),event);
						});
						//$(document).on('mouseover','.CodeMirror *',function(event){
						//	LiveEditor.uiTools.uiCmStateManager($('.CodeMirror'),'show');
						//});
						//$(document).on('mouseout','.CodeMirror',function(event){
						//	LiveEditor.uiTools.uiCmStateManager($('.CodeMirror'),'hide');
						//});
						$(document).on('mouseover','.elInf2', function(event){
							LiveEditor.uiTools.uiCmStateManager($('.CodeMirror'),'show');
						});
						$(document).on('mouseover','.jPicker', function(event){
							LiveEditor.uiTools.uiCmStateManager($('.CodeMirror'),'show');
						});
						$(document).on('mouseover','iframe',function(event){
							LiveEditor.uiTools.uiCmStateManager($('.CodeMirror'),'hide');
						});
						$(document).on('mouseover', '#cmDeadSpace', function(event){
							LiveEditor.uiTools.uiCmStateManager($('.CodeMirror'),'show');
						});

						//Replication here from restore state - doesnt work without itfor some reason
						//TODO We could turn these into functions so they can be called in more DRY way
						//Picker
						LiveEditor.jobManager.restoreActiveJobUIState('colorPicker');
						LiveEditor.jobManager.restoreActiveJobUIState('codeMirrorPin');
						LiveEditor.jobManager.restoreActiveJobUIState('baselineGridX');
						LiveEditor.jobManager.restoreActiveJobUIState('baselineGridY');

						LiveEditor.setWorkArea();
						$('#loadingBlock').fadeOut();
						//$('#liveEditor'+activeJobId).show();

						LiveEditor.jobTools.jobList.applyContentJobListNav();
						
						/*-DEBUG-B-*/
						if(debuggery){
							$('#liveEditor'+activeJobId).contents().on('scroll',function(){
								scroll_debugger(activeJobId);
							});
						}
						/*-DEBUG-E-*/
						
			});
			if(typeof LiveEditor.activationManager.editors[activeJobId] != "undefined"){
				if(typeof LiveEditor.activationManager.editors[activeJobId].textarea != "undefined"){
					LiveEditor.activationManager.editors[activeJobId].textarea.show();
				}
			}

		} else {
			
			$('#loadingBlock').show();
			
			var activeJobId = LiveEditor.jobManager.getActiveJobId();
			var textarea = $('<textarea class="'+LiveEditor.common.cssEditorTextareaRef+'" id="'+LiveEditor.common.cssEditorTextareaRef+''+activeJobId+'"></textarea>');
			$('BODY').append(textarea);
			myCodeMirror = new LiveEditor.codeMirror.init(activeJobId);
			
			var doc = opener.myCodeMirror.cm.getDoc();
			var linkedDoc = doc.linkedDoc({sharedHist: true});
			myCodeMirror.cm.swapDoc(linkedDoc);
			
			child_window_resize();
			
			$('#loadingBlock').fadeOut();
			
		}
		
	};
	LiveEditor.activationManager.activatePage = function(jobId){
		var job = LiveEditor.jobManager.jobs[jobId];

		//alert(job.toSource());
		var href = 'http://'+job.url+'/'+job.default_uri;
		if(!LiveEditor.activationManager.httpResponse( href )){
			alert('Invalid page!');
		}
		
		//Bind keboard shortcuts
		$('#liveEditor'+jobId).contents().bind("keyup keydown keypress", function(e) {
			window.parent.LiveEditor.layoutTools.inspector.keyLoadInspector(e);
			window.parent.LiveEditor.uiTools.codeMirrorPin.keyTogglePin(e);
		});

		//LiveEditor.activationManager.activationStatusCheck(job);

		if(LiveEditor.activationManager.iframeModifiedContentCheck(job,'uriModifier') == 0){
			LiveEditor.activationManager.uriModifier(job);
		}
		LiveEditor.activationManager.stylesheetProcessor(job);

	};
	LiveEditor.activationManager.iframeModifiedContentCheck = function(job,content){

		//due to iframe content cacheing, a job which has previously been used may load its last cached version
		//if cached content is loaded then we do not want to reprocess the page
		//we identify whether a page has been processed or not by prepending the html with some custom tag identifiers
		//this function checks for the presence of a custom tag with a particular id
		var regex = new RegExp(content, 'g');
		var match = 0;
		var sourceContents = $('#liveEditor'+job.id).contents();
		sourceContents.find(LiveEditor.common.iframeModifiedContentTag).each(function(i,v){
			if($(this).attr('id').match(regex)){
				match = 1;
			}
		});
		return match;

	};
	LiveEditor.activationManager.uriModifier = function(job){

		var replacement = '/'+job.url;
		var sourceContents = $('#liveEditor'+job.id).contents();

		sourceContents.find('html').prepend( LiveEditor.activationManager.custom_id_tag('uriModifier') );

		//base href
		var href = sourceContents.find('base').attr('href');
		if(typeof href != "undefined"){
			$.each(job.activeData.urlVariants,function(i,v){
				if(href.match(v)){
					sourceContents.find('base').attr('href', href.replace(v, replacement));
				}
			});
		}// else {
			//WHAT ABOUT FORCING A NEW BASE HREF REGARDLESS OF WHAT IS ON THE PAGE??
			//sourceContents.find('head').prepend('<base href="/cms">');
		//}
		////sourceContents.find('head').prepend('<base href="/mysite.co.uk" />');

		//<link href>
		sourceContents.find('link').each(function(){
			uri_attr_replace( $(this), 'href', job.activeData.urlVariants, replacement );
		});
		//<a href>
		sourceContents.find('a').each(function(){
			uri_attr_replace( $(this), 'href', job.activeData.urlVariants, replacement );
			//bind click ref to each link
			var href = $(this).attr('href');
			$(this).bind(LiveEditor.common.iframeClickRef, {href: href}, function(event) {
				LiveEditor.activationManager.clickHandler(job.id,href);
			});
		});
		//<form action>
		sourceContents.find('form').each(function(){
			uri_attr_replace( $(this), 'action', job.activeData.urlVariants, replacement );
		});
		//<img src>
		sourceContents.find('img').each(function(){
		    uri_attr_replace( $(this), 'src', job.activeData.urlVariants, replacement );
		});


	};
	//LiveEditor.sourceContentsCheck = function(content){
		//Not Found
		//The requested URL /leisurematic.co.uk/cms/home was not found on this server.
	//}
	LiveEditor.activationManager.stylesheetProcessor = function(job){

		var sourceContents = $('#liveEditor'+job.id).contents();

		//LiveEditor.sourceContentsCheck(sourceContents);
		
		//alert(sourceContents.find('html').html().toSource());

		//check stylesheet match
		var ssMatchCount = 0;
		var modified = LiveEditor.activationManager.iframeModifiedContentCheck(job,'stylesheetProcessor');
		var link = null;
		var css = null;
		
		sourceContents.find('link').each(function(){
			if($(this).attr('href').indexOf(job.stylesheet_uri) != -1){
				ssMatchCount++;
				link = $(this);
			}
		});


		if(ssMatchCount == 0){
			LiveEditor.activationManager.stylesheetStatus(job.id,'NO_MATCH_FOUND');
		} else if (ssMatchCount > 1){
			LiveEditor.activationManager.stylesheetStatus(job.id,'MULTIPLE_MATCHES_FOUND');
		} else if (ssMatchCount == 1){
			LiveEditor.activationManager.stylesheetStatus(job.id,'MATCH_FOUND_OK');
		}

		if(modified == 1){
			ssMatchCount = 1;
			LiveEditor.activationManager.stylesheetStatus(job.id,'ALREADY_ACTIVATED');
		}

		//create a css textarea for this job
		LiveEditor.activationManager.editors[job.id].textarea = $('<textarea class="'+LiveEditor.common.cssEditorTextareaRef+'" id="'+LiveEditor.common.cssEditorTextareaRef+''+job.id+'"></textarea>');
		$('body').append(LiveEditor.activationManager.editors[job.id].textarea);

		//if a match is found process the CSS
		if(ssMatchCount == 1){

			//gather the live stylesheet and preprocessor file contents
			var stylesheet_contents = false;
			var preprocessor_contents = false;
			thribber.manager('topstrip','add','Getting '+LiveEditor.common.cssModes[job.css_mode]+' stylesheet data...');
			//TODO this method gets both RAW CSS and PREPROCESSOR contents if the mode is greater than 1. decide if this is useful (e.g. possibility is Parsed preprocessed CSS/Live CSS code comparison)
			$.get( LiveEditor.common.paths.scrapeJobStyleData+'/'+job.id , function( data ) {
				var dataJson = JSON.parse(data);
				if(typeof dataJson.stylesheet_data.error != "undefined"){
					thribber.manager('topstrip','remove',null,{error: dataJson.stylesheet_data.message });
				} else {
					stylesheet_contents = dataJson.stylesheet_data;
					thribber.manager('topstrip','remove');
				}
				if(typeof dataJson.preprocessor_data.error != "undefined"){
					thribber.manager('topstrip','remove',null,{error: dataJson.preprocessor_data.message });
				} else {
					preprocessor_contents = dataJson.preprocessor_data;
					thribber.manager('topstrip','remove');
				}
			});

			//check no modified css is currently in play from a previous uri for this job
			if(typeof LiveEditor.activationManager.editors[job.id].css == "undefined"){
				//get contents of stylesheet or scss and place it inside the textarea ready for editing
				if(job.css_mode == 1){
					LiveEditor.activationManager.editors[job.id].css = '/* (CSS) NO CONTENT */';
					if(stylesheet_contents !== false){
						LiveEditor.activationManager.editors[job.id].css = LiveEditor.activationManager.cssParser(job,stylesheet_contents);
					}
					/*var ssUrl = job.stylesheet_uri_root+'/'+job.stylesheet_uri;
					thribber.manager('topstrip','add','(CSS) Getting stylesheet data from '+ssUrl+'...');
					$.get( ssUrl , function( data ) {
						LiveEditor.activationManager.editors[job.id].css = LiveEditor.activationManager.cssParser(job,data);
						thribber.manager('topstrip','remove');
						flash.kill('error');
					})
					.fail(function() {
						thribber.manager('topstrip','remove',null,{error:'Failed to load '+ssUrl+'. Check the file exists'});
					});*/
				} else if(job.css_mode == 2){
					LiveEditor.activationManager.editors[job.id].scss = '/* (SCSS) NO CONTENT */';
					if(preprocessor_contents !== false){
						LiveEditor.activationManager.editors[job.id].scss = LiveEditor.activationManager.cssParser(job,preprocessor_contents);
						var rawCss = process_scss( LiveEditor.activationManager.editors[job.id].scss );
						LiveEditor.activationManager.editors[job.id].css = LiveEditor.activationManager.cssParser(job,rawCss);
					}
					/*var stylesheet_uri_scss = job.stylesheet_uri.substring(0, job.stylesheet_uri.length - 3) + 'scss';
					if(job.preprocessor_uri != ''){
						stylesheet_uri_scss = job.preprocessor_uri;
					}
					var ssUrl = job.preprocessor_uri_root+'/'+stylesheet_uri_scss;
					thribber.manager('topstrip','add','(SCSS) Getting stylesheet data from '+ssUrl+'...');
					$.get( ssUrl , function( data ) {
						LiveEditor.activationManager.editors[job.id].scss = LiveEditor.activationManager.scssParser(job,data,stylesheet_uri_scss);
						rawCss = process_scss( LiveEditor.activationManager.editors[job.id].scss );
						LiveEditor.activationManager.editors[job.id].css = LiveEditor.activationManager.cssParser(job,rawCss);
						thribber.manager('topstrip','remove');
						flash.kill('error');
					})
					.fail(function() {
						thribber.manager('topstrip','remove',null,{error:'Failed to load '+ssUrl+'. Check the file exists'});
					});*/
				} else if(job.css_mode == 3){
					LiveEditor.activationManager.editors[job.id].less = '/* (LESS) NO CONTENT */';
					if(preprocessor_contents !== false){
						LiveEditor.activationManager.editors[job.id].less = LiveEditor.activationManager.cssParser(job,preprocessor_contents);
						var rawCss = process_less( LiveEditor.activationManager.editors[job.id].less );
						LiveEditor.activationManager.editors[job.id].css = LiveEditor.activationManager.cssParser(job,rawCss);
					}
					/*var stylesheet_uri_less = job.stylesheet_uri.substring(0, job.stylesheet_uri.length - 3) + 'less';
					if(job.preprocessor_uri != ''){
						stylesheet_uri_less = job.preprocessor_uri;
					}
					var ssUrl = job.preprocessor_uri_root+'/'+stylesheet_uri_less;
					thribber.manager('topstrip','add','(LESS) Getting stylesheet data from '+ssUrl+'...');
					$.get( ssUrl , function( data ) {
						LiveEditor.activationManager.editors[job.id].less = LiveEditor.activationManager.lessParser(job,data,stylesheet_uri_less);
						rawCss = process_less( LiveEditor.activationManager.editors[job.id].less );
						LiveEditor.activationManager.editors[job.id].css = LiveEditor.activationManager.cssParser(job,rawCss);
						thribber.manager('topstrip','remove');
						flash.kill('error');
					})
					.fail(function() {
						thribber.manager('topstrip','remove',null,{error:'Failed to load '+ssUrl+'. Check the file exists'});
					});*/
				}
				thribber.manager('topstrip','remove');

			}


			if(job.css_mode == 1){
				LiveEditor.activationManager.editors[job.id].textarea.val( LiveEditor.activationManager.editors[job.id].css );
			} else if(job.css_mode == 2){
				LiveEditor.activationManager.editors[job.id].textarea.val( LiveEditor.activationManager.editors[job.id].scss );
			} else if(job.css_mode == 3){
				LiveEditor.activationManager.editors[job.id].textarea.val( LiveEditor.activationManager.editors[job.id].less );
			}

			if(modified == 0){

				sourceContents.find('html').prepend( LiveEditor.activationManager.custom_id_tag('stylesheetProcessor') );

				//replace the original link with the contents of the stylesheet inline
				var replacement = '/'+LiveEditor.jobManager.jobs[jobId].url;
				css = "\n<!-- B Job "+job.id+" -->\n<style id='jobId"+job.id+"'>\n"+css_url_replace( LiveEditor.activationManager.editors[job.id].css, LiveEditor.jobManager.jobs[jobId].activeData.urlVariants, replacement, job.id )+"\n</style>\n<!-- E Job "+job.id+" -->\n\n";

				link.after(css);

				//remove the original link
				//TODO a toggle switch maybe useful here to compare old code with new code
				link.remove();

			}



			//link up the editor to the inline css
			LiveEditor.activationManager.editors[job.id].textarea.on('keyup',function(){

				var sourceContents = $('#liveEditor'+job.id).contents();
				var inlineCss = sourceContents.find('style#jobId'+job.id);
				if(job.css_mode == 1){
					LiveEditor.activationManager.editors[job.id].css = $(this).val();
				} else if(job.css_mode == 2){
					$.ajaxSetup({ async: true });
					LiveEditor.activationManager.editors[job.id].scss = $(this).val();
					rawCss = process_scss(LiveEditor.activationManager.editors[job.id].scss);
					LiveEditor.activationManager.editors[job.id].css = LiveEditor.activationManager.cssParser(job,rawCss);
				} else if(job.css_mode == 3){
					$.ajaxSetup({ async: true });
					LiveEditor.activationManager.editors[job.id].less = $(this).val();
					rawCss = process_less(LiveEditor.activationManager.editors[job.id].less);
					LiveEditor.activationManager.editors[job.id].css = LiveEditor.activationManager.cssParser(job,rawCss);
				}

				var replacement = '/'+LiveEditor.jobManager.jobs[job.id].url;
				var css = css_url_replace( LiveEditor.activationManager.editors[job.id].css, job.activeData.urlVariants, replacement, job.id );
				inlineCss.text( css );
				/*-DEBUG-B-*/
				if(debuggery){
					LiveEditor.activationManager.debug( job.id );
				}
				/*-DEBUG-E-*/
			});

		} else {

			flash.manager('error','A link to the following stylesheet was not found in the remote site: '+job.stylesheet_uri_root+'/'+job.stylesheet_uri);//,true

		}

	};
	LiveEditor.activationManager.cssParser = function(job,data){
		var signature = "/* CSS Job Information\n * Name: "+job.name+"\n * Stylesheet URI: http://"+job.url+'/'+job.stylesheet_uri+"\n * \n * "+job.description+"\n */\n\n";
		var replacement = '/'+job.url;
		css = data.replace(signature,'');
		css = signature + css;
		return css;
	}
	LiveEditor.activationManager.scssParser = function(job,data,stylesheet_uri_scss){
		var signature = "/* SCSS Job Information\n * Name: "+job.name+"\n * Stylesheet URI: http://"+job.url+'/'+stylesheet_uri_scss+"\n * \n * "+job.description+"\n */\n\n";
		var replacement = '/'+job.url;
		css = data.replace(signature,'');
		css = signature + css;
		return css;
	}
	LiveEditor.activationManager.lessParser = function(job,data,stylesheet_uri_less){
		var signature = "/* LESS Job Information\n * Name: "+job.name+"\n * Stylesheet URI: http://"+job.url+'/'+stylesheet_uri_less+"\n * \n * "+job.description+"\n */\n\n";
		var replacement = '/'+job.url;
		css = data.replace(signature,'');
		css = signature + css;
		return css;
	}
	LiveEditor.activationManager.clickHandler = function(jobId,href){
		if(!LiveEditor.activationManager.httpResponse( href )){
			alert('Invalid page!');
		}
		////$('#liveEditor'+jobId).hide();

		//Write history
		LiveEditor.remoteTools.customUri.applyUri( href );

	};
	LiveEditor.activationManager.stylesheetStatus = function(jobId,status){
		/*-DEBUG-B-*/
		if(debuggery){
			$('.db4').remove();
			debug(4,'Job '+jobId+' Stylesheet status',status);
		}
		/*-DEBUG-E-*/
	};
	LiveEditor.activationManager.debug = function(jobId){
		var job = LiveEditor.jobManager.jobs[jobId];
		var sourceContents = $('#liveEditor'+jobId).contents();
		var code = 'CANNOT ACCESS CONTENT';
		if(typeof sourceContents.find('html').html() != "undefined"){
			code = sourceContents.find('html').html();
			$('#liveEditor'+jobId).addClass('activated');
			code = code.replace(/textarea/g,'div');
		}
		/*-DEBUG-B-*/
		if(debuggery){
			$('.db3').remove();
			debug(3,'Iframe HTML','<textarea>'+code+'</textarea>');
		}
		/*-DEBUG-E-*/

	};
	LiveEditor.activationManager.custom_id_tag = function(str){
		return '<'+LiveEditor.common.iframeModifiedContentTag+' id="'+str+'" />'+"\n";
	};
	LiveEditor.activationManager.httpResponse = function(uri){

		//TODO maybe cache the results of this so we dont have to lookup previously passed pages (could be bad in case of server outage)

		//Check HTTP response code
		var response = false;
		var dataJson = {};
		thribber.manager('topstrip','add','Loading page...');
		$.post( LiveEditor.common.paths.httpResponse, uri).done(function(data){
			dataJson = JSON.parse(data);
			if(typeof dataJson['error'] == "undefined"){
				response = true;
			}
			thribber.manager('topstrip','remove');
		});

		/*-DEBUG-B-*/
		if(debuggery){
			$('.db5').remove();
			out = uri + "<br />";
			$.each(dataJson, function(i,v){
				out += i +' : ' + v + "<br />"
			});
			debug(5,'HTTP Response',out);
		}
		/*-DEBUG-E-*/

		return response;
	};
	function getCompletions(token, context) {
		  var found = [], start = token.string;
		  function maybeAdd(str) {
		    if (str.indexOf(start) == 0) found.push(str);
		  }
		  function gatherCompletions(obj) {
		    if (typeof obj == "string") forEach(stringProps, maybeAdd);
		    else if (obj instanceof Array) forEach(arrayProps, maybeAdd);
		    else if (obj instanceof Function) forEach(funcProps, maybeAdd);
		    for (var name in obj) maybeAdd(name);
		  }

		  if (context) {
		    // If this is a property, see if it belongs to some object we can
		    // find in the current environment.
		    var obj = context.pop(), base;
		    if (obj.className == "js-variable")
		      base = window[obj.string];
		    else if (obj.className == "js-string")
		      base = "";
		    else if (obj.className == "js-atom")
		      base = 1;
		    while (base != null && context.length)
		      base = base[context.pop().string];
		    if (base != null) gatherCompletions(base);
		  }
		  else {
		    // If not, just look in the window object and any local scope
		    // (reading into JS mode internals to get at the local variables)
		    for (var v = token.state.localVars; v; v = v.next) maybeAdd(v.name);
		    gatherCompletions(window);
		    forEach(keywords, maybeAdd);
		  }
		  return found;
		}
	//CodeMirror
	LiveEditor.codeMirror = {};
	LiveEditor.codeMirror.init = function(jobId){

		$('.CodeMirror').remove();
		$('#cmDeadSpace').remove();
		$('BODY').append('<div id="cmDeadSpace" />');

		if(LiveEditor.jobManager.jobStates.data[jobId].codeMirror == null){
			LiveEditor.jobManager.jobStates.data[jobId].codeMirror = {};
		}

		$('link#cmTheme').remove();
		$('head').append('<link id="cmTheme" rel="stylesheet" href="js/codemirror/theme/'+LiveEditor.common.cmThemes[LiveEditor.common.uiState.data.cmDefaultTheme]+'.css"/>');
		//this.textarea = document.getElementById(LiveEditor.common.cssEditorTextareaRef+'1');

		var layoutMode = LiveEditor.jobManager.getJobState(jobId,'layoutMode');

		this.cm = CodeMirror(document.body, {
			jobId: jobId,
			value: document.getElementById(LiveEditor.common.cssEditorTextareaRef+jobId).value,
			textarea: $('#'+LiveEditor.common.cssEditorTextareaRef+jobId),//document.getElementById(LiveEditor.common.cssEditorTextareaRef+jobId),
			lineNumbers: true,
			lineWrapping: true,
			styleActiveLine: true,
			autoCloseTags: true,
			extraKeys: {
				'Ctrl-Space' : 'autocomplete',
				'Cmd-.' : 'toggleComment',
				'Ctrl-.' : 'toggleComment',
				'Alt-Down' : function(cm){
					cm.lineBlockManipulate(cm,'down','swap');
				},
				'Alt-Up' : function(cm){
					cm.lineBlockManipulate(cm,'up','swap');
				},
				'Ctrl-Alt-Down' : function(cm){
					cm.lineBlockManipulate(cm,'down','copy');
				},
				'Ctrl-Alt-Up' : function(cm){
					cm.lineBlockManipulate(cm,'up','copy');
				}
			},
			theme: LiveEditor.common.cmThemes[LiveEditor.common.uiState.data.cmDefaultTheme],
			mode: {name: "css"},
			myW: LiveEditor.common.layoutModes[layoutMode].cmDefaultW,
			myH: LiveEditor.common.layoutModes[layoutMode].cmDefaultH,
			layoutModes : {
				0 : {
					myW: LiveEditor.common.layoutModes[0].cmDefaultW,
					myH: LiveEditor.common.layoutModes[0].cmDefaultH
				},
				1 : {
					myW: LiveEditor.common.layoutModes[1].cmDefaultW,
					myH: LiveEditor.common.layoutModes[1].cmDefaultH
				}
			}
		    //fullLines: false
			//viewportMargin: 'Infinity', //allows browser search to work on entire doc rather than only that which is in view
			//highlightSelectionMatches: {showToken: /\w/}
		});
		this.cm.lineBlockManipulate = function(cm,direction,mode){

			var head = cm.getCursor('head');
			var anchor = cm.getCursor('anchor');
			if(anchor.line > head.line){
				var lineFirst = cm.getCursor('head');
				var lineLast = cm.getCursor('anchor');
				head.ch = 0;
				anchor.ch = null;//setting null tells cm to select the last char of the line
			} else {
				var lineFirst = cm.getCursor('anchor');
				var lineLast = cm.getCursor('head');
				head.ch = null;//setting null tells cm to select the last char of the line
				anchor.ch = 0;
			}

			if(lineFirst.line == 0 && direction == 'up'){
				return false;
			}

			var selectedLines = {};
			selectedLines.diff = lineLast.line - lineFirst.line;
			selectedLines.lines = [lineFirst.line];
			selectedLines.content = [ cm.getLine(lineFirst.line) ];
			if(selectedLines.diff > 0){
				var copyFill = '';
				for(i = 1; i <= selectedLines.diff; i++){
					var lineNo = (lineFirst.line + i);
					selectedLines.content.push( cm.getLine(lineNo) );
					copyFill += "\n";
				}

				if(mode == 'swap'){

					if(direction == 'up'){
						var newContent = selectedLines.content.join("\n")+copyFill;
						var oldContent = cm.getLine(lineFirst.line-1);
					} else {
						var newContent = copyFill+selectedLines.content.join("\n");
						var oldContent = cm.getLine(lineLast.line+1);
					}

					var ha = head;
					var aa = anchor;
					if(direction == 'up'){
						ha.line--;
						aa.line--;
					} else {
						ha.line++;
						aa.line++;
					}
					cm.replaceRange(newContent,ha,aa);

					var hb = ha;
					var ab = aa;
					if(direction == 'up'){
						hb.line += i;
						ab.line += i;
					} else {
						hb.line--;
						ab.line--;
					}
					cm.replaceRange(oldContent,hb,ab);

					//set new selection
					if(direction == 'up'){
						head.line-=i;
						anchor.line-=i;
					} else {
						head.line += 1;
						anchor.line += 1;
					}
					cm.setSelection(head,anchor);

				} else if (mode == 'copy'){

					var newContent = selectedLines.content.join("\n") + "\n" + selectedLines.content.join("\n");
					cm.replaceRange(newContent,head,anchor);

					//set new selection
					if(direction == 'down'){
						head.line += i;
						anchor.line += i;
					}
					cm.setSelection(head,anchor);

				}



			} else {
				//if the target is a single line then select the entire line
				var h = {line:lineFirst.line, ch:0};
				var a = {line:lineFirst.line, ch:null};
				cm.setSelection(h,a);

				if(mode == 'swap'){

					if(direction == 'up'){
						var newContent = selectedLines.content.join("\n");
						var oldContent = cm.getLine(lineFirst.line-1);
					} else {
						var newContent = selectedLines.content.join("\n");
						var oldContent = cm.getLine(lineFirst.line+1);
					}
					cm.replaceSelection(oldContent,h,a);
					if(direction == 'up'){
						h.line--;
						a.line--;
					} else {
						h.line++;
						a.line++;
					}

					cm.setSelection(h,a);
					cm.replaceSelection(newContent,h,a);

				}  else if (mode == 'copy'){

					var newContent = selectedLines.content.join("\n") + "\n" + selectedLines.content.join("\n");
					cm.replaceSelection(newContent,h,a);

					if(direction == 'down'){
						h.line++;
						a.line++;
					}

					cm.setSelection(h,a);
				}

			}


			/*-DEBUG-B-*/
			if(debuggery){
				var out = '';
				out += 'oldcontent:' + oldContent + '<br />';
				out += 'head:' + head.toSource() + '<br />';
				out += 'anchor:' + anchor.toSource() + '<br />';
				out += 'selectedLines.diff:' + selectedLines.diff + '<br />';
				out += 'selectedLines.lines:' + selectedLines.lines.toSource() + '<br />';
				out += 'selectedLines.content:' + selectedLines.content.toSource() + '<br />';
				debug(9,'TEST',out);
			}
			/*-DEBUG-E-*/
		}

		//Process changes to codemirror including keyup
		this.cm.on("change", function(cm){
		
			if(appData.mode == 1){

				var cmValue = cm.getValue();

				cm.options.textarea.val(cmValue);

				//TODO need to check if editing is permitted, i.e. all chacks have been passed
				if(LiveEditor.jobManager.jobs[jobId].css_mode == 1){

					var sourceContents = $('#liveEditor'+cm.options.jobId).contents();
					var inlineCss = sourceContents.find('style#jobId'+cm.options.jobId);
					LiveEditor.activationManager.editors[cm.options.jobId].css = cmValue;
					var replacement = '/'+LiveEditor.jobManager.jobs[jobId].url;
					var css = css_url_replace( LiveEditor.activationManager.editors[cm.options.jobId].css, LiveEditor.jobManager.jobs[jobId].activeData.urlVariants, replacement, jobId );
					inlineCss.text( css );
					LiveEditor.activationManager.debug( jobId );

				} else if(LiveEditor.jobManager.jobs[jobId].css_mode == 2 && LiveEditor.common.preProcessRealtime == true){

					LiveEditor.activationManager.editors[cm.options.jobId].scss = cmValue;
					delay(function(){cm.process_scss(cmValue);}, LiveEditor.common.preProcessDelay);

				} else if(LiveEditor.jobManager.jobs[jobId].css_mode == 3 && LiveEditor.common.preProcessRealtime == true){

					//$.ajaxSetup({ async: true });
					//if(CssProcessor.parseStatus == 0){
						//CssProcessor.editStatus = 1;
						//if(CssProcessor.status == 0){
							LiveEditor.activationManager.editors[cm.options.jobId].less = cmValue;
							delay(function(){cm.process_less(cmValue);}, LiveEditor.common.preProcessDelay);
							//CssProcessor.status = 0;
						//}
					//}

				}
			
			}

		});

		this.cm.process_scss = function(scss){

			var jobId = myCodeMirror.cm.options.jobId;
			var sourceContents = appData.ifrLocation.$('#liveEditor'+jobId).contents(); //IAH need to target the iframe in the parent window when in fukll window mode
			var inlineCss = sourceContents.find('style#jobId'+jobId);

			var replacement = '/'+LiveEditor.jobManager.jobs[jobId].url;
			var css = process_scss(scss);
			css = css_url_replace( css, LiveEditor.jobManager.jobs[jobId].activeData.urlVariants, replacement, jobId );
			inlineCss.text( css );

			LiveEditor.activationManager.debug( jobId );

		}

		this.cm.process_less = function(less){

			var jobId = myCodeMirror.cm.options.jobId;
			var sourceContents = appData.ifrLocation.$('#liveEditor'+jobId).contents();
			var inlineCss = sourceContents.find('style#jobId'+jobId);

			var replacement = '/'+LiveEditor.jobManager.jobs[jobId].url;
			var css = process_less(less);
			css = css_url_replace( css, LiveEditor.jobManager.jobs[jobId].activeData.urlVariants, replacement, jobId );
			inlineCss.text( css );

			LiveEditor.activationManager.debug( jobId );

		}

		this.cm.on("dblclick", function(cm){

			var h = cm.getCursor('head');
			var a = cm.getCursor('anchor');
			var precedingCh = cm.getRange({line:a.line, ch:a.ch-1},{line:a.line, ch:a.ch});
			if(precedingCh == '#' || precedingCh == '$'){
				cm.setSelection( {line:a.line, ch:a.ch-1},{line:h.line, ch:h.ch} );
			}

		});

		this.cm.on("keyup", function(cm,e) {

			//reset the timer for any keypress even if it does not trigger an update
			//delay(function(){}, LiveEditor.common.preProcessDelay);
		
			var regex = new RegExp("^[a-zA-Z0-9]+$");
		    var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if(!e.ctrlKey && !e.shiftKey && !e.altKey){
				if (regex.test(key)) {
					cm.showHint(e);
				}
			}

			//DEBUG
			if(typeof keyupTrace == "undefined"){
				keyupTrace = '';
			}
			LiveEditor.codeMirror.txtarea_keyup($.event.fix(e));
		});

		this.cm.on("keydown", function(cm,e) {
			//CTRL+S keymap. Prevent default browser behaviour
			if(e.ctrlKey && (e.which == 83)) {
				$.ajaxSetup({ async: true });
				css_progress_manager( cm.options.jobId );
				$.ajaxSetup({ async: false });
				e.preventDefault();
				return false;
			}
		});

		this.cm.on("cursorActivity", function(cm,e) {

			cm.manageState(cm);

			var head = cm.getCursor('head');
			var anchor = cm.getCursor('anchor');
			if(head.line == anchor.line){
				if(anchor.ch > head.ch){
					var a = cm.getCursor('head');
					var h = cm.getCursor('anchor');
				} else {
					var a = cm.getCursor('anchor');
					var h = cm.getCursor('head');
				}
				cm.manageStrings(cm.getRange(a,h));
				
				if(appData.mode == 2){
					opener.myCodeMirror.cm.setCursor(a,h);
				}

				//sync parent cursor to popup
				/*if(typeof popup != "undefined"){
					if(popup.myCodeMirror.cm != null){
						popup.myCodeMirror.cm.setCursor(a,h);
					}
				}*/
			} else {
			
				if(appData.mode == 2){
					opener.myCodeMirror.cm.setSelection(head,anchor);
				}
			
			}
			
			
			
			
			
			/*-DEBUG-B-*/
			if(debuggery){
				var out = 'Anch: ' + cm.getCursor('anchor').toSource()+'<br />';
				out += 'Head: ' + cm.getCursor('head').toSource()+'<br />';
				out += 'A: ' + a.toSource()+'<br />';
				out += 'H: ' + h.toSource()+'<br />';
				out += 'Default text height: ' + cm.defaultTextHeight()+'<br />';
				out += '<textarea id="cmSync" class="liveCss">'+cm.options.textarea.val()+'</textarea>';
				debug(6,'CodeMirror',out);
			}
			/*-DEBUG-E-*/
		});

		this.cm.on("scroll", function(cm,e) {
			cm.manageState(cm);
		});

		this.cm.manageStrings = function(string){

			//Hex codes
			if(string.charAt(0) == '#' && (string.length == 4 || string.length == 7) ){
				strOut = string.substring(1);
				if(string.length == 4){
					strOut = string.substring(1)+string.substring(1);
				}
				$.jPicker.List[0].color.active.val('hex', strOut, 'cmSource');
			}

		};

		this.cm.manageState = function(cm,mode){

			var layoutMode = LiveEditor.jobManager.getJobState(jobId,'layoutMode');

			if(typeof mode == "undefined"){mode = 'w';}
			if(mode == 'w'){
////alert('cmWrite:'+jobId);

				cm.options.layoutModes[layoutMode].myW = cm.options.myW;
				cm.options.layoutModes[layoutMode].myH = cm.options.myH;

				LiveEditor.jobManager.jobStatesManager(jobId, 'codeMirror', {
					anchor : cm.getCursor('anchor'),
					head : cm.getCursor('head'),
					//selection : cm.getSelection(),
					scrollInfo : cm.getScrollInfo(),
					size : {'w':cm.options.myW,'h':cm.options.myH},
					//size : {'w':cm.options.layoutModes[layoutMode].myW,'h':cm.options.layoutModes[layoutMode].myH},
					layoutModes : cm.options.layoutModes
				});
			} else if (mode == 'r'){

				var cmState = LiveEditor.jobManager.jobStates.data[jobId].codeMirror;

				if(cmState.size != null){
					cm.options.myW = cmState.size.w;
					cm.options.myH = cmState.size.h;

					cm.options.layoutModes = cmState.layoutModes;

					cm.setSize(cmState.size.w,cmState.size.h);
				} else {
					cm.setSize(cm.options.myW,cm.options.myH);
				}
				if(cmState.anchor != null && cmState.head != null){

					if(cmState.anchor.line == cmState.head.line && cmState.anchor.ch == cmState.head.ch){
						//cursor only - no selection
						cm.setCursor({line:cmState.anchor.line, ch:cmState.anchor.ch});
					} else {
						//selection
						cm.setSelection({line:cmState.anchor.line, ch:cmState.anchor.ch},{line:cmState.head.line, ch:cmState.head.ch});
					}

				}
				if(cmState.scrollInfo != null){
					cm.scrollTo(cmState.scrollInfo.left,cmState.scrollInfo.top);
				}
				cm.deadSpace(layoutMode);

			}

		};
		this.cm.applyContent = function(cm,content,postTranferCursorPos){
			var cmState = LiveEditor.jobManager.jobStates.data[jobId].codeMirror;
			if(cmState.anchor != null && cmState.head != null){
				cm.replaceSelection(content,postTranferCursorPos);
			} else {
				alert('Please specify an insertion point');
			}
		};

		//Sync the textarea to the codemirror
		this.cm.options.textarea.on('keyup',function(){
			myCodeMirror.cm.setValue($(this).val());
		});

		this.cm.switchDims = function(layoutMode){

			//remember the previous values
			switch(layoutMode){
				case 0:
					myCodeMirror.cm.options.layoutModes[1].myW = myCodeMirror.cm.options.myW;
					myCodeMirror.cm.options.layoutModes[1].myH = myCodeMirror.cm.options.myH;
					break;
				case 1:
					myCodeMirror.cm.options.layoutModes[0].myW = myCodeMirror.cm.options.myW;
					myCodeMirror.cm.options.layoutModes[0].myH = myCodeMirror.cm.options.myH;
					break;
			}

			myCodeMirror.cm.options.myW = myCodeMirror.cm.options.layoutModes[layoutMode].myW;
			myCodeMirror.cm.options.myH = myCodeMirror.cm.options.layoutModes[layoutMode].myH;

		};

		this.cm.deadSpace = function(layoutMode){
			if(layoutMode == 0){
				$('#cmDeadSpace').css('width',LiveEditor.common.cmDeadSpaceW).css('height',LiveEditor.common.cmDeadSpaceH).css('left',AppSettings.common.uiDims.workArea.marginL+"px");
			} else if(layoutMode == 1){
				$('#cmDeadSpace').css('width',LiveEditor.common.cmDeadSpaceH).css('height',window.innerHeight-AppSettings.common.uiDims.workArea.marginT-AppSettings.common.uiDims.workArea.marginB+"px").css('left',AppSettings.common.uiDims.workArea.marginL+"px");
			}
		};

		//New window mode
		this.cm.newWindow = function(cm) {



			/*var win2;

			function openSecondaryWindow() {
				return win2 = window.open('win2.htm', 'popup', 'width=800,height=150');
			}

			function flash() {
				$('body').css('border', '50px solid red').animate({
					'border-color': 'blue'
				});
			}

			$(function() {

				if (!openSecondaryWindow()) $(document.body).prepend('<a href="#">Popup blocked.  Click here to open the secondary window.</a>').click(function() {
					openSecondaryWindow();
					return false;
				});

				$('#inc').click(function() {
					if (win2) win2.increment();
					else alert('The secondary window is not open.');
					return false;
				});
			});*/


			//METHID 1
			//we can use the linkedDOc method but each time a key is pressed, we need to check that the jobId of the parent matches the child
			//if they dont match then the linked doc needs to be rebuilt. downside is we will need to rebuild all the tools within the new window
			/*
			var popupOpts = "fullscreen=no";
			popup = window.open ("","popup",popupOpts),
			html  ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			html += '<scr'+'ipt src="'+APP_BASE_URI+'js/jquery/jquery.min.js"></scr'+'ipt>';
			html += '<scr'+'ipt src="'+APP_BASE_URI+'js/jquery-ui/jquery-ui.min.js"></scr'+'ipt>';
			html += '<scr'+'ipt src="'+APP_BASE_URI+'js/jquery/plugins/jquery-cookie/src/jquery.cookie.js"></scr'+'ipt>';
			html += '<scr'+'ipt src="'+APP_BASE_URI+'js/jquery/plugins/jquery-validate/dist/jquery.validate.min.js"></scr'+'ipt>';
			html += '<scr'+'ipt src="'+APP_BASE_URI+'js/codemirror/lib/codemirror.js"></scr'+'ipt>';
			html += '<scr'+'ipt src="'+APP_BASE_URI+'js/codemirror/mode/css/css.js"></scr'+'ipt>';
			html += '<li'+'nk rel="stylesheet" href="'+APP_BASE_URI+'js/codemirror/lib/codemirror.css"/>';
			html += '<li'+'nk rel="stylesheet" href="'+APP_BASE_URI+'js/codemirror/addon/dialog/dialog.css"/>';
			html += '<li'+'nk rel="stylesheet" href="'+APP_BASE_URI+'js/codemirror/theme/obsidi-dan.css"/>';
			html += '<li'+'nk rel="stylesheet" href="'+APP_BASE_URI+'css/codemirror.custom.css"/>';
			html += '<style>BODY {font-size:11px; color:#191919; margin:0}</style>';
			html += '</head>';
			html += '<b'+'ody>';
			//html += '<div id="throbber">LOADING...</div>';
			html += '<div id="content"></div>';
			html += '<scr'+'ipt>';
			//html += 'document.getElementById("content").innerHTML = opener.myCodeMirror.cm.options.toSource();';
			html += 'var doc = opener.myCodeMirror.cm.getDoc();';
			html += 'var linkedDoc = doc.linkedDoc({sharedHist: true});';
			html += 'var cm2 = CodeMirror(document.body, {lineNumbers: true});';
			html += 'cm2.swapDoc(linkedDoc);';
			html += 'document.getElementById("content").innerHTML = "JobId:" + opener.myCodeMirror.cm.options.jobId;';
			//html += '$(function(){$("#throbber").hide();});';
			html += '</sc'+'ript>';
			html += '</b'+'ody>';
			popup.document.write(html);
			//popup.document.close();
			*/
			
			//METHID 2
			//Another method to try...
			//adding a index/0/2 query to the url could load the relevant liveEditor but only the tools, not the iframe.
			//this would mean everything is loaded in the new window and we can then sync the two together using the #CodeMirror DOM element instead of the actual object
			//(the object gets destroyed and rebuilt frequently)
			//win2 on keyup... look for opener.documentElementById('Codemirror') get keyup contents and populate opener.documentElementById('Codemirror')
			//when win2 is open, add a class of parentWin to the parents body. We can then use this to hide stuff in the parent window if needed

			var popupOpts = "fullscreen=no";
			popup = window.open ("<?php echo Configure::read('APP_BASE_URI');?>LiveEditors/index/0/2","popup",popupOpts);
			/*html = '<scr'+'ipt>';
			html += '$(function(){';
			html += 'var doc = opener.myCodeMirror.cm.getDoc();';
			html += 'var linkedDoc = doc.linkedDoc({sharedHist: true});';
			//html += 'var cm2 = myCodeMirror;';
			html += 'myCodeMirror.swapDoc(linkedDoc);';
			html += '});';
			html += '</sc'+'ript>';*/
			//popup.document.write(html);
			
		};

		$('.CodeMirror').prepend('<div class="sizeHandle" id="sizeHandleCmY" />');



		/*if(typeof popup != 'undefined'){
			if(!popup.closed){
				popup.document.getElementsByTagName("BODY")[0].innerHTML = "";
				this.cm.newWindow(this.cm);
			}
		}*/


		/*function debugCursor(event){
			out = '';
			out += 'ScreenX: ' + event.screenX;
			out += 'ScreenY: ' + event.screenY;
			debug(8,'UI CSS',out);
		}*/

		//this.cm.defaultTextHeight(14);

		//Reinstate last editor state
		this.cm.manageState(this.cm,'r');
		this.cm.focus();

		//CUSTOMISE HINTS
		/*var orig = CodeMirror.hint.cssprops;
		CodeMirror.hint.cssprops = function(myCodeMirror) {
		  var inner = orig(myCodeMirror) || {from: myCodeMirror.getCursor(), to: myCodeMirror.getCursor(), list: []};
		  inner.list.push("bozo");
		  inner.list.push("doop");
		  return inner;
		};*/
		/* //useful
		this.cm.findWordAt({line, ch});
		*/

	};
	LiveEditor.codeMirror.txtarea_keyup = function(e){

		/*-DEBUG-B-*/
		if(debuggery){
			if(e.key == ' '){e.key = 'Space'}
			keyupTrace += '<span class="key">' + e.key + '<span class="keycode">' + e.keyCode + '</span>&nbsp;&nbsp;</span>';
			var out = 'Keycode: <span class="key">' + e.key + '<span class="keycode">' + e.keyCode + '</span>&nbsp;&nbsp;</span><br />';
			out += 'Key trace: ' + keyupTrace;
			debug(7,'Input',out);
		}
		/*-DEBUG-E-*/
	}


	function debug(no,title,data){
		$('.db'+no).remove();
		$('#debug div#debug-'+no).append('<div class="debug db'+no+'"><span class="title">'+title+'</span><br />'+data+'</div>');
		$('#debug ul.tabNav li.tab'+no+' a').append('<span class="db'+no+'">'+title+'</span>');
	}

	/* Persist Data
	 * Stores editor state as JSON files so it can be reinstaed later
	 * object.name
	 * object.data
	 * mode 'w' 'r' or 'd', defaults to w if no
	 */
	 function persist_data(object,mode){

		//read/write the data
		if(typeof mode == "undefined"){mode = 'w';}
		var modes = {'w':'write','r':'read','d':'delete'};
		var status = false;
		if(mode == 'd'){
			$.removeCookie(object.name);
		}
		thribber.manager('topstrip','add',modes[mode]+' '+object.name+'...');
		$.post(LiveEditor.common.paths.dataStateManager + '/' + object.name + '/' + mode, JSON.stringify(object)).done(function(data){
			data = JSON.parse(data);
			if(typeof data.error == "undefined" && typeof data.success == "undefined"){
				$.cookie(object.name,JSON.stringify(data));
				status = true;
			}
			thribber.manager('topstrip','remove');
		});
		return status;
	}

	function css_progress_manager(jobId,mode){

		//read/write the data
		//var cssData = $('#'+LiveEditor.common.cssEditorTextareaRef+jobId).val();
		var cssData = myCodeMirror.cm.getValue();
		var ftp = '';
		var ftpMsg = '';
		if(typeof mode == "undefined"){
			mode = 'w';
		} else if(mode == 'ftp'){
			mode = 'w';
			ftp = true;
			ftpMsg = 'Publishing via FTP...';
		}
		var modes = {'w':'write','r':'read','d':'delete'};
		var status = false;
		thribber.manager('topstrip','add',modes[mode]+' CSS data...'+ftpMsg);
		$.post(LiveEditor.common.paths.cssProgressManager + '/' + jobId + '/' + mode + '/' + ftp, JSON.stringify(cssData)).done(function(data){

			var dataJson = {};

			try {
				dataJson = JSON.parse(data);
		    } catch (e) {

		    }

			if(typeof dataJson.error != "undefined"){
				thribber.manager('topstrip','remove',null,{error: dataJson.message });
			} else {
				status = true;
				if(typeof dataJson.error != "undefined"){
					if(!ftp){
						thribber.manager('topstrip','remove',null,{success: "Saved CSS data" });
					} else {
						thribber.manager('topstrip','remove',null,{success: "Saved and published CSS data" });
					}
				}
			}


			if(mode == 'r'){
				myCodeMirror.cm.setValue(data);
			}
			thribber.manager('topstrip','remove');

			if(LiveEditor.jobManager.jobs[jobId].css_mode == 2){
				myCodeMirror.cm.process_scss(cssData);
			} else if(LiveEditor.jobManager.jobs[jobId].css_mode == 3){
				myCodeMirror.cm.process_less(cssData);
			}
			
		});
		
		return status;
	}

	function css_backup_manager(jobId,mode,key){

		//read/write the data
		//var cssData = $('#'+LiveEditor.common.cssEditorTextareaRef+jobId).val();
		var cssData = myCodeMirror.cm.getValue();
		if(typeof key == "undefined"){
			mode = 'w';
			key = '';		}
		if(typeof mode == "undefined"){
			mode = 'r';
		}
		var modes = {'w':'write backup','r':'read backup','d':'delete backup'};
		var status = false;
		thribber.manager('topstrip','add',modes[mode]+' of CSS data...');
		$.post(LiveEditor.common.paths.cssBackupManager + '/' + jobId + '/' + mode + '/' + key, JSON.stringify(cssData)).done(function(data){

			var dataJson = {};

			try {
				dataJson = JSON.parse(data);
		    } catch (e) {

		    }

			if(typeof dataJson.error != "undefined"){
				thribber.manager('topstrip','remove',null,{error: dataJson.message });
			} else {
				status = true;
				if(typeof dataJson.error != "undefined"){
					thribber.manager('topstrip','remove',null,{success: "Backup of CSS data completed" });
				}
			}


			if(mode == 'r'){
				myCodeMirror.cm.setValue(data);
			}
			thribber.manager('topstrip','remove');

			if(LiveEditor.jobManager.jobs[jobId].css_mode == 2){
				myCodeMirror.cm.process_scss(cssData);
			} else if(LiveEditor.jobManager.jobs[jobId].css_mode == 3){
				myCodeMirror.cm.process_less(cssData);
			}
			
		});
		
		return status;
	}

	function save_data_state(){
		persist_data(LiveEditor.jobManager.jobStatuses);
		persist_data(LiveEditor.jobManager.jobStates);
		persist_data(LiveEditor.common.uiState);
		css_progress_manager(LiveEditor.jobManager.getActiveJobId());
	}

	function cached_site_uri(jobId){
		var job = LiveEditor.jobManager.jobs[jobId];
		var cachedSiteDir = LiveEditor.common.paths.cachedSiteDir+'/'+job.id+'/'+job.url+'/'+job.url;
		var buildIndexPageGetterUrl = LiveEditor.common.paths.buildIndexPageGetter+'/'+jobId;
		var localisedIndexPage = false;
		$.get(buildIndexPageGetterUrl,function(data){
			localisedIndexPage = data;
		});
		if(localisedIndexPage != 0){
			cachedSiteDir = cachedSiteDir + '/'+job.default_uri+'/'+localisedIndexPage;
			//alert(cachedSiteDir);
			return cachedSiteDir;
		} else {
			return false;
		}
	}

	function uri_maker(jobId){

		if(LiveEditor.jobManager.jobStates.data[jobId] != null){
			if(LiveEditor.jobManager.jobStates.data[jobId].cached){
				var cachedSiteUri = cached_site_uri(jobId);
				if(cachedSiteUri){
					return cachedSiteUri;
				} else {
					//IAH, when reverting meed to undo the link activation
					alert('No cached site was found. Build one via the job manager. Reverting to live site default URI');
					LiveEditor.jobManager.jobStatesManager(jobId,'cached',false);
					return false;
				}
			}
		}

		var uri = LiveEditor.common.paths.proxyUrlPrefix + '/' + LiveEditor.jobManager.jobs[jobId].url;

		if(LiveEditor.jobManager.jobStates.data[jobId].default_uri != null){
			var uriCheck = uri +  '/'+LiveEditor.jobManager.jobStates.data[jobId].default_uri;

			if(!LiveEditor.activationManager.httpResponse( uriCheck )){
				LiveEditor.jobManager.jobStatesManager(jobId,'default_uri','invalid');
				uri += '/'+LiveEditor.jobManager.jobs[jobId].default_uri;
				alert("\nInvalid page! Redirecting to default URI.\n\nInvalid URI: " + uriCheck + "\n\nNew URI: " + uri);
			} else {
				uri = uriCheck;
			}

		} else {
			uri += '/'+LiveEditor.jobManager.jobs[jobId].default_uri;
		}
		return uri;
	}

	function uri_variants(uri){
		return ['http://'+uri,'https://'+uri,'http://www.'+uri,'https://www.'+uri];
	}

	function uri_attr_replace(tag,attr,findArr,replacement){
		var attrVal = tag.attr(attr);
		if(typeof attrVal != "undefined"){

			if(attrVal.charAt(0) == '/'){
				//TODO what about those which do not begin forward slash??
				//replace relative urls
				////var newAttrVal = 'http://' + myUrlReplace + attrVal.substring(1);
				var newAttrVal = replacement + '/' + attrVal.substring(1);
				tag.attr(attr, newAttrVal);

			} else {
				//replace absolute urls
				hrefReplaced = false;
				$.each(findArr,function(i,v){
					if(attrVal.match(v)){
						var newAttrVal = attrVal.replace(v, replacement);
						tag.attr(attr, newAttrVal);
						hrefReplaced = true;
					}
				});
				if(hrefReplaced == false){
					if(attrVal.match('http://') && tag.get(0).nodeName == 'A'){
						tag.unbind(LiveEditor.common.iframeClickRef);
						/*tag.removeAttr(attr); //remove external link hrefs
						tag.bind("click", function(event) {
							event.preventDefault();
							event.stopPropagation();
							return false;
						});*/
					} else {

					}
				}
			}
		}
	}

	function css_url_replace(css,findArr,replacement, jobId){

		//Test code
		/*css = "url(/dan.com/page.png)"+"\n";
		css += "url( /dan.com/page.png )"+"\n";
		css += 'url( "/dan.com/page.png" )'+"\n";
		css += "url(  '/dan.com/page.png'  )"+"\n";
		css += "url('/dan.com/page.png')"+"\n";*/

		if(typeof css == "undefined"){
			flash.manager('error','No CSS available');
			css = "";
		}

		css = css.replace(/url\(\s*/gi,"url(");
		css = css.replace(/url\(\//gi,"url("+replacement+'/');
		css = css.replace(/url\(\"\//gi,'url("'+replacement+'/');
		css = css.replace(/url\(\'\//gi,"url('"+replacement+'/');
		css = css.replace(/url\(\'\//gi,"url('"+replacement+'/');
		//IAH need a relative replace function which grabs the dir of the stylesheet_uri as the replacement
		//try passing in an object as replace,ent, one for absolute and one for relative urls
		//css = css.replace(/url\(\..\//gi,"url("+'/'+LiveEditor.jobManager.jobs[jobId].url+'/tpl_grayscale/css'+'/../');

		return css;

	}

	function process_scss(scss){

		var output = false;
		thribber.manager('topstrip','add','PROCESS SCSS...');

		var jobId = LiveEditor.jobManager.getActiveJobId();
		var job = LiveEditor.jobManager.jobs[jobId];
		var path = job.preprocessor_uri_root+'/'+job.preprocessor_uri;
		var input = {'code':scss,'path':path};
		$.post(LiveEditor.common.paths.scssProcessor, input).done(function(data){

			var dataJson = {};

			try {
				dataJson = JSON.parse(data);
		    } catch (e) {

		    }

			if(typeof dataJson.error != "undefined"){
				output = "/* (!) Error processing SCSS data\n * "+dataJson.message+"\n */";
				thribber.manager('topstrip','remove',null,{error: dataJson.message });
			} else {
				output = data;
				thribber.manager('topstrip','remove');
				flash.kill('error');
			}
		});
		$.ajaxSetup({ async: false });
		return output;

	}

	function process_less(less){

		//CssProcessor.parseStatus = 1;

		var output = false;
		thribber.manager('topstrip','add','PROCESS LESS...');

		var jobId = LiveEditor.jobManager.getActiveJobId();
		var job = LiveEditor.jobManager.jobs[jobId];
		var path = job.preprocessor_uri_root+'/'+job.preprocessor_uri;
		var input = {'code':less,'path':path};
		$.post(LiveEditor.common.paths.lessProcessor, input).done(function(data){

			var dataJson = {};

			try {
				dataJson = JSON.parse(data);
		    } catch (e) {

		    }

			if(typeof dataJson.error != "undefined"){
				output = "/* (!) Error processing LESS data\n * "+dataJson.message+"\n */";
				thribber.manager('topstrip','remove',null,{error: dataJson.message });
			} else {
				output = data;
				thribber.manager('topstrip','remove');
				flash.kill('error');
			}

			//CssProcessor.parseStatus = 0;
			
		});
		$.ajaxSetup({ async: false });
		return output;

	}

	function parent_window_resize(){
		LiveEditor.setWorkArea();
	}
	
	function child_window_resize(){
		myCodeMirror.cm.options.myH = window.innerHeight+'px';
		myCodeMirror.cm.options.myW = (window.innerWidth-24) +'px';
		myCodeMirror.cm.manageState(myCodeMirror.cm,'w');
		myCodeMirror.cm.manageState(myCodeMirror.cm,'r');
	}

	//uiColorPicker
	function bind_ui_color_picker(){
		var colors = $('input.color').colorPicker({
			customBG: '#222',
			readOnly: true,
			margin: {left: 50, top: -4},
			size: 1,
			init: function(elm, colors) { // colors is a different instance (not connected to colorPicker)
			  elm.style.backgroundColor = elm.value;
			  elm.style.color = colors.rgbaMixCustom.luminance > 0.22 ? '#222' : '#ddd';
			}
		})
		.each(function(idx, elm) {
			$(elm).css({'background-color': this.value})
		});
	}
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
	jQuery.fn.reverse = [].reverse;
	/*prevent ctrl+s open save dialog
	$(document).bind('keydown', function(e) {
	  if(e.ctrlKey && (e.which == 83)) {
	    e.preventDefault();
	    alert('Ctrl+S');
	    return false;
	  }
	});*/
	$(function(){

		$(document).mousedown(function(event){
			mouseDown = true;
		});
		$(document).mouseup(function(event){
			mouseDown = false;
		});

		//jPicker
		$('body').append('<input id="Callbacks" type="hidden" value="'+LiveEditor.common.jPickerInitColor+'" />');
		$('#Callbacks').jPicker({},
	        function(color,context){
				var hex = color.val('hex');
			},
			function(color, context){
				var hex = color.val('hex');
				var hexVal = '#'+hex.toUpperCase();
				if(context != 'cmSource'){
					LiveEditor.layoutTools.inspector.transferContent(hexVal,'around');
				}
			}
		);
		
	

		//UI
		LiveEditor.common.uiCssParser();

		LiveEditor.jobManager.initJobs();

		$('#content').append('<div id="ctrlSetsL" class="ctrlSets" />');
		$('#content').append('<div id="ctrlSetsR" class="ctrlSets" />');
		$('#ctrlSetsL').append('<div id="ctrlSet0" class="ctrlSet" />');
		$('#ctrlSetsL').append('<div id="ctrlSet1" class="ctrlSet" />');
		$('#ctrlSetsL').append('<div id="ctrlSet2" class="ctrlSet" />');
		$('#ctrlSetsL').append('<div id="ctrlSet3" class="ctrlSet" />');
		$('#ctrlSetsL').append('<div id="ctrlSet4" class="ctrlSet" />');
		$('#ctrlSetsL').append('<div id="ctrlSet5" class="ctrlSet" />');
		$('#ctrlSetsL').append('<div id="ctrlSet6" class="ctrlSet" />');

		$('BODY').append('<div id="edgeBarL" class="edgeBar"><a href="#" class="edgeBarClose"><span class="glyphicon glyphicon-remove floatR"></span></a><div id="edgeBarInner"></div></div>');

		var uiStyles = '<style>';
		uiStyles += '.topBar ul.myNav li {line-height:'+AppSettings.common.uiDims.heights.topBar+'px}';
		uiStyles += '.thribber {line-height:'+AppSettings.common.uiDims.heights.uiBaseline+'px}';
		uiStyles += '.thribber span {min-height:'+(AppSettings.common.uiDims.heights.uiBaseline*1.5)+'px}';
		uiStyles += 'ul#screenWidths li a {width:'+AppSettings.common.uiDims.heights.uiBaseline+'px}';
		uiStyles += 'ul#screenWidths li a {line-height:'+AppSettings.common.uiDims.heights.uiBaseline+'px}';
		//uiStyles += '#ctrlSetsL {top:'+AppSettings.common.uiDims.heights.uiBaseline+'px}';
		uiStyles += '.ctrlSet a {width:'+AppSettings.common.uiDims.heights.uiBaseline+'px}';
		uiStyles += '.ctrlSet a {line-height:'+(AppSettings.common.uiDims.heights.uiBaseline-1)+'px}';
		uiStyles += '</style>';
		$('head').append(uiStyles);


		if(appData.mode == 1){
		
			LiveEditor.jobTools.jobList.tool();

			LiveEditor.remoteTools.cssCodeManager.tool();
			LiveEditor.remoteTools.reloadCurrent.tool();
			LiveEditor.remoteTools.newWindowCurrent.tool();
			LiveEditor.remoteTools.customUri.tool();
			LiveEditor.jobTools.saveDataState.tool();
			LiveEditor.jobTools.resetAll.tool();
			LiveEditor.remoteTools.useCached.tool();

			LiveEditor.jobTools.backupManager.tool();
			
			LiveEditor.appTools.uiSettings.tool();
			LiveEditor.appTools.help.tool();

			LiveEditor.layoutTools.deviceSimulator.tool();
			LiveEditor.layoutTools.inspector.tool();
			LiveEditor.layoutTools.baselineGrid.tool();
			LiveEditor.layoutTools.colorPicker.tool();
			LiveEditor.layoutTools.jobLayoutAssets.tool();

			LiveEditor.uiTools.codeMirrorFontSize.tool();
			LiveEditor.uiTools.codeMirrorTheme.tool();
			LiveEditor.uiTools.codeMirrorPin.tool();
			LiveEditor.uiTools.layoutMode.tool();
			//LiveEditor.uiTools.codeMirrorHeight.tool();
			LiveEditor.uiTools.codeMirrorNewWindow.tool();
			LiveEditor.uiTools.uiBackgroundFill.tool();
			
			$(window).resize(function() {
				parent_window_resize();
			});

			window.onbeforeunload = function() {
				if(typeof popup != 'undefined'){
					if(!popup.closed){
						popup.close();
						popup = undefined;
					}
				}
				//return "Dude, are you sure you want to leave? Think of the kittens!";
			}
			
		} else if(appData.mode == 2){
		
			LiveEditor.remoteTools.cssCodeManager.tool();
			LiveEditor.layoutTools.colorPicker.tool();
			LiveEditor.layoutTools.jobLayoutAssets.tool();

			LiveEditor.jobTools.backupManager.tool();
			
			$(window).resize(function() {
				child_window_resize();
			});
			
			$(document).on('mouseover','body',function(){
				if(opener == null){
					alert("Main editor window is no longer available.\nThis window will now close");
					window.close();
				}
			});

		}
		
		LiveEditor.jobManager.restoreActiveJobUIState();
		
		LiveEditor.activationManager.init();

		//work area manipulation
		LiveEditor.uiTools.edgeBar.tool();

		$(document).on('click','#navJobs A.navJob',function(){
			//alert($(this).parent().attr('data-job'));
			LiveEditor.common.activeJobId = false;
			var job = {'id':$(this).parent().attr('data-job')};
			LiveEditor.jobManager.activateJob(job);
			LiveEditor.activationManager.init();
			return false;
		});

		$(document).on('click','#navJobs A.navJobClose',function(){
			LiveEditor.common.activeJobId = false;
			var job = {'id':$(this).parent().attr('data-job')};
			LiveEditor.jobManager.jobStatusManager(job.id,0);
			LiveEditor.activationManager.init();
			//TODO destroy activation for this job LiveEditor.activationManager.deactivate();
			return false;
		});

		bind_ui_color_picker();

	});

	var delay = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();

	//$(window).load(function(){
		//thribber.manager('fullscreen','remove');
	//});

	/*function isObject(myVar,notNull){
		if(typeof notNull == "undefined"){
			notNull = true;
		}
		if(notNull){
			if(myVar !== null && typeof myVar === 'object'){
				return true;
			}
		} else {
			if(typeof myVar === 'object'){
				return true;
			}
		}
		return false;
	}*/

	function isObject(val) {
	    if (val === null) { return false;}
	    return ( (typeof val === 'function') || (typeof val === 'object') );
	}

	function dim_value(value,unit){
		return value + unit;
	}

</script>


<script>
$(function() {

	/*-DEBUG-B-*/
	if(debuggery){
		$('#ctrlSet6').append("<div class='ctrl ctrlButton' id='ctrlDebug'><a class='btn btn-default btn-xs' title='Debug'><span class='glyphpro glyphpro-flash'></span></a></div>");

		if($.cookie('debugActive') == 1){
			$('#debug').addClass('active');
			$('#ctrlDebug a').addClass('active');
		}
		$('#ctrlDebug a').on('click',function(){
			if($('#debug').hasClass('active')){
				$('#debug').removeClass('active');
				$.removeCookie('debugActive');
				$(this).removeClass('active');
			} else {
				$('#debug').addClass('active');
				$.cookie('debugActive',1);
				$(this).addClass('active');
			}
		});
		$( "#debug" ).tabs({
			active   : $.cookie('debugTab'),
			activate : function( event, ui ){
				$.cookie( 'debugTab', ui.newTab.index(),{
					expires : 10
				});
			}
		});
	}
	/*-DEBUG-E-*/

	$(document).on('click','.itemClick a',function(){
		CodeMirror.commands["find"](myCodeMirror.cm);
		$('.CodeMirror-search-field').val( $(this).text() );
		//myCodeMirror.cm.doSearch(myCodeMirror.cm,true);
		//CodeMirror.commands["find"](myCodeMirror.cm);
		//myCodeMirror.cm.getSearchCursor($(this).text()).findNext();
		//$('.CodeMirror-search-field').trigger( jQuery.Event('keypress', {which: 83}) );
		//doSearch
		//"find", "findPrev", "clearSearch", "replace", and"replaceAll"
		return false;
	});

});
function scroll_debugger(jobId){
	var iframe = $('#liveEditor'+jobId);
	var offsetTop = LiveEditor.common.iframeTopOffset;
	var offsetBottom = LiveEditor.common.iframeBottomOffset;
	var cmHeight = parseInt( myCodeMirror.cm.options.myH.replace("px",'') );
	var iframeContentHeight = iframe.contents().scrollTop() + (window.innerHeight - cmHeight - offsetBottom - offsetTop - AppSettings.common.uiDims.heights.uiBaseline);
	if(LiveEditor.jobManager.getJobState(jobId,'screenWidthId') == 1){
		iframeContentHeight = iframe.contents().scrollTop() + (window.innerHeight - cmHeight - AppSettings.common.uiDims.heights.uiBaseline);
	}
	var out = '';
	out += 'scrollTop: ' + iframe.contents().scrollTop() + '<br />';
	out += 'cmHeight: ' + cmHeight + '<br />';
	out += 'iframeContentHeight: ' + iframeContentHeight + '<br />';
	out += 'iframe / cm diff: ' + (iframe.contents().height() - cmHeight) + '<br />';
	out += 'If iframe >= cm we force scroll to bottom';
	debug(9,'Scroll info',out);
}
</script>

<script>
	

</script>
