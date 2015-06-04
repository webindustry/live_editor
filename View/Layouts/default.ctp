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
	<?php echo $this->Html->meta('icon') ?>

	<?php //jquery source
	echo $this->Html->script('jquery/jquery.min');?>

	<?php //jquery source
	echo $this->Html->script('jquery-ui/jquery-ui.min');?>
	
	<?php //jquery cookie
	echo $this->Html->script('jquery/plugins/jquery-cookie/src/jquery.cookie');?>
	
	<?php //jquery validate
	echo $this->Html->script('jquery/plugins/jquery-validate/dist/jquery.validate.min');?>
		
	<?php //bootstrap
    echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">'."\n";
	echo '<meta name="viewport" content="width=device-width, initial-scale=1">'."\n";
    echo $this->Html->css('bootstrap.min.css')."\n";
	echo $this->Html->css('bootstrap-offcanvas.css')."\n";
	echo $this->Html->script('bootstrap.min.js')."\n";
	echo $this->Html->script('bootstrap.offcanvas.js')."\n";
	?>

	<?php //jquery ui
	echo $this->Html->css('../js/jquery-ui/themes/smoothness/jquery-ui.min')."\n";
	//echo $this->Html->css('../js/jquery-ui/themes/smoothness/theme');?>
	
	<?php //cake generic
	echo $this->Html->css('cake.generic');?>
	
	<?php //cake bootstrap wi
	echo $this->Html->css('cake.bootstrap.wi');?>

	<?php //app custom styles
	echo $this->Html->css('app.custom');?>
	
	<?php echo $this->fetch('meta') ?>
	<?php echo $this->fetch('css') ?>
	<?php echo $this->fetch('script') ?>
	
	<?php echo $this->element('thribber')?>
</head>
<body>
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
