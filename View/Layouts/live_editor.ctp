<?php 
$this->append('script');
echo $jsVars;
$this->end();
//$this->MyAjax->my_ajax_view_code();
?><!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset() ?>
	<title>
		<?php echo $this->fetch('title') ?>
	</title>
	
	<?php 
	//if($mode >1){
	//	echo '<base href="'.Configure::read('APP_BASE_URI').'" />';
	//}
	?>
	
	<?php echo $this->Html->meta('icon') ?>

	<?php //jquery source
	echo $this->Html->script('jquery/jquery.min');?>

	<?php //jquery ui source
	echo $this->Html->script('jquery-ui/jquery-ui.min');?>
	
	<?php //jquery cookie
	echo $this->Html->script('jquery/plugins/jquery-cookie/src/jquery.cookie');?>
	<script>
		$.cookie.defaults.path = '/';
	</script>
	
	<?php //jquery validate
	echo $this->Html->script('jquery/plugins/jquery-validate/dist/jquery.validate.min');?>
	
	<?php //jquery jPicker
	echo $this->Html->script('jquery/plugins/jpicker/jpicker-1.1.6.custom.min')."\n";
	echo $this->Html->css('../js/jquery/plugins/jpicker/css/jpicker-1.1.6')."\n";
	echo $this->Html->css('jpicker.custom')."\n";?>
	
	<?php //codemirror
	echo $this->Html->script('codemirror/lib/codemirror')."\n";
	echo $this->Html->script('codemirror/mode/css/css')."\n";
	echo $this->Html->script('codemirror/addon/hint/show-hint')."\n";
	echo $this->Html->script('codemirror/addon/hint/css-hint')."\n";
	//echo $this->Html->script('codemirror/addon/hint/anyword-hint')."\n";
	echo $this->Html->script('codemirror/addon/comment/comment')."\n";
	echo $this->Html->script('codemirror/addon/dialog/dialog')."\n";
	echo $this->Html->script('codemirror/addon/search/search')."\n";
	echo $this->Html->script('codemirror/addon/search/searchcursor')."\n";
	echo $this->Html->script('codemirror/addon/search/match-highlighter')."\n";
	echo $this->Html->css('../js/codemirror/lib/codemirror')."\n";
	echo $this->Html->css('../js/codemirror/addon/dialog/dialog')."\n";
	echo $this->Html->css('../js/codemirror/addon/hint/show-hint')."\n";
	echo $this->Html->css('../js/codemirror/theme/obsidi-dan')."\n";
	echo $this->Html->css('codemirror.custom')."\n";
	?>
	<script>
	//Cant get this to work so setting it manually in show-hint.js
	//CodeMirror.commands.autocomplete = function(cm) {
   	//	cm.showHint({hint: CodeMirror.hint.css, completeSingle: false});
   	//}
	</script>
		
	<?php //bootstrap
    echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">'."\n";
	echo '<meta name="viewport" content="width=device-width, initial-scale=1">'."\n";
    echo $this->Html->css('bootstrap.min.css')."\n";
    echo $this->Html->css('glyphicons.files.css')."\n";
    echo $this->Html->css('glyphicons.pro.css')."\n";
    echo $this->Html->css('glyphicons.social.css')."\n";
    //echo $this->S3Content->css('bootstrap-theme.min.css')."\n";
	echo $this->Html->css('bootstrap-offcanvas.css')."\n";
	echo $this->Html->script('bootstrap.min.js')."\n";
	echo $this->Html->script('bootstrap.offcanvas.js')."\n";
	?>

	<?php //jquery ui
	echo $this->Html->css('../js/jquery-ui/themes/smoothness/jquery-ui.min')."\n";
	//echo $this->Html->css('../js/jquery-ui/themes/smoothness/theme');?>
	
	<?php //cake bootstrap wi
	echo $this->Html->css('cake.bootstrap.wi');?>

	<?php //app custom styles
	echo $this->Html->css('app.custom');?>
	
	<?php echo $this->fetch('meta') ?>
	<?php echo $this->fetch('css') ?>
	<?php echo $this->fetch('script') ?>
	
	<?php echo $this->element('thribber')?>
</head>
<body id="liveEditor">
	<div id="container">
		<div id="content">
			<?php echo $this->Session->flash(); ?>

			<?php echo $this->fetch('content') ?>
		</div>
		<div id="footer">
		</div>
	</div>
</body>
</html>
