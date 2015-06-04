<div class="jobs view">
	<h2><?php __('Job') ?></h2>
	<dl>
		<dt><?php __('Id') ?></dt>
		<dd>
			<?php h($job->id) ?>
			&nbsp;
		</dd>
		<dt><?php __('Name') ?></dt>
		<dd>
			<?php h($job->name) ?>
			&nbsp;
		</dd>
		<dt><?php __('Description') ?></dt>
		<dd>
			<?php h($job->description) ?>
			&nbsp;
		</dd>
		<dt><?php __('Url') ?></dt>
		<dd>
			<?php h($job->url) ?>
			&nbsp;
		</dd>
		<dt><?php __('Default Uri') ?></dt>
		<dd>
			<?php h($job->default_uri) ?>
			&nbsp;
		</dd>
		<dt><?php __('Stylesheet Uri') ?></dt>
		<dd>
			<?php h($job->stylesheet_uri) ?>
			&nbsp;
		</dd>
		<dt><?php __('Ftp Account') ?></dt>
		<dd>
			<?php $job->has('ftp_account') ? $this->Html->link($job->ftp_account->name, ['controller' => 'FtpAccounts', 'action' => 'view', $job->ftp_account->id]) : '' ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php $this->Html->link(__('Edit Job'), ['action' => 'edit', $job->id]) ?> </li>
		<li><?php $this->Form->postLink(__('Delete Job'), ['action' => 'delete', $job->id], ['confirm' => __('Are you sure you want to delete # %s?', $job->id)]) ?> </li>
		<li><?php $this->Html->link(__('List Jobs'), ['action' => 'index']) ?> </li>
		<li><?php $this->Html->link(__('New Job'), ['action' => 'add']) ?> </li>
		<li><?php $this->Html->link(__('List FtpAccounts'), ['controller' => 'FtpAccounts', 'action' => 'index']) ?> </li>
		<li><?php $this->Html->link(__('New Ftp Account'), ['controller' => 'FtpAccounts', 'action' => 'add']) ?> </li>
	</ul>
</div>
