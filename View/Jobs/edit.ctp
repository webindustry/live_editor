<div class="jobs form">
<?php echo $this->Form->create('Job') ?>
	<fieldset>
		<legend><?php echo __('Edit Job'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('description');
		echo $this->Form->input('url');
		echo $this->Form->input('default_uri');
		
		echo $this->Form->input('codebase_config');
		
		echo $this->Form->input('stylesheet_uri_root');
		echo $this->Form->input('stylesheet_uri');
		echo '<p>stylesheet_uri is used for matching purposes so that the system can detect the presence of the stylehseet in the website';
		echo $this->Form->input('preprocessor_uri_root');
		echo $this->Form->input('preprocessor_uri');
		echo '<p>preprocessor_uri is used for matching purposes so that the system can detect the presence of the stylehseet in the website';
		echo $this->Form->input('css_mode', array('options' => $cssModes));
		echo $this->Form->input('ftp_account_id', array('options' => $ftpAccounts));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true)) ?>

</div>
<div class="actions">
	<h3><?php __('Actions') ?></h3>
	<?php echo $this->element('Navigation/side_nav') ?>
</div>
