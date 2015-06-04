<div class="ftpAccounts form">
<?php echo $this->Form->create('FtpAccount'); ?>
	<fieldset>
		<legend><?php echo __('Add Ftp Account'); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('ftp_host');
		echo $this->Form->input('ftp_user');
		echo $this->Form->input('ftp_pass', array('type' => 'password'));
		echo $this->Form->input('ftp_path');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>