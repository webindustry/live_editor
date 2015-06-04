<div class="ftpAccounts index">
	<h2><?php echo __('Ftp Accounts'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('ftp_host'); ?></th>
			<th><?php echo $this->Paginator->sort('ftp_user'); ?></th>
			<th><?php echo $this->Paginator->sort('ftp_path'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($ftpAccounts as $ftpAccount): ?>
	<tr>
		<td><?php echo h($ftpAccount['FtpAccount']['id']); ?>&nbsp;</td>
		<td><?php echo h($ftpAccount['FtpAccount']['name']); ?>&nbsp;</td>
		<td><?php echo h($ftpAccount['FtpAccount']['ftp_host']); ?>&nbsp;</td>
		<td><?php echo h($ftpAccount['FtpAccount']['ftp_user']); ?>&nbsp;</td>
		<td><?php echo h($ftpAccount['FtpAccount']['ftp_path']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Copy'), ['action' => 'copy_row', 'FtpAccount', 'name', $ftpAccount['FtpAccount']['id']]) ?>
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $ftpAccount['FtpAccount']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $ftpAccount['FtpAccount']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $ftpAccount['FtpAccount']['id']), array(), __('Are you sure you want to delete # %s?', $ftpAccount['FtpAccount']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>

</div>
<div class="actions">
	<h3><?php echo __('Actions') ?></h3>
	<?php echo $this->element('Navigation/side_nav') ?>
</div>
