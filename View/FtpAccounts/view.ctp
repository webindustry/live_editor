<div class="ftpAccounts view">
<h2><?php echo __('Ftp Account'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($ftpAccount['FtpAccount']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($ftpAccount['FtpAccount']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Ftp Host'); ?></dt>
		<dd>
			<?php echo h($ftpAccount['FtpAccount']['ftp_host']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Ftp User'); ?></dt>
		<dd>
			<?php echo h($ftpAccount['FtpAccount']['ftp_user']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Ftp Path'); ?></dt>
		<dd>
			<?php echo h($ftpAccount['FtpAccount']['ftp_path']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Ftp Account'), array('action' => 'edit', $ftpAccount['FtpAccount']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Ftp Account'), array('action' => 'delete', $ftpAccount['FtpAccount']['id']), array(), __('Are you sure you want to delete # %s?', $ftpAccount['FtpAccount']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Ftp Accounts'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Ftp Account'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Jobs'), array('controller' => 'jobs', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Job'), array('controller' => 'jobs', 'action' => 'add')); ?> </li>
	</ul>
</div>
