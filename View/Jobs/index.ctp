<script>
function check_build(jobId){
	var url = APP_BASE_URI+'jobs/poll_build/'+jobId;
	$.get(url,function(data){
		if(data == 1){
			$('#buildThribber'+jobId+'').remove();
			$('.itemId'+jobId+' td.status').prepend('<span id="buildThribber'+jobId+'" class="buildThribber"><?php echo $this->Html->image('throbber16.png', array('title' => "Building local site..."));?></span>');
			check_build(jobId);
		} else {
			$('#buildThribber'+jobId+'').remove();
		}
	});
}
</script>

<div class="jobs index">
	<h2><?php echo __('Jobs') ?> <?php echo $this->Html->link(__('Add+'), ['controller' => 'Jobs', 'action' => 'add'], ['class' => 'floatR']) ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
		<th width="50px">&nbsp;</th>
		<th><?php echo $this->Paginator->sort('name') ?></th>
		<th><?php echo $this->Paginator->sort('url') ?></th>
		<!-- <th><?php echo $this->Paginator->sort('ftp_account_id') ?></th> -->
		<th><?php echo $this->Paginator->sort('css_mode') ?></th>
		<th class="actions" width="100px"><?php __('Actions') ?></th>
		<th><?php echo $this->Paginator->sort('id','#') ?></th>
	</tr>
	<?php foreach ($jobs as $job): ?>
	<tr class="itemId<?php echo $job['Job']['id'] ?>">
		<td class="status">&nbsp;</td>
		<td>
		<strong><?php echo h($job['Job']['name']) ?></strong><br />
		<?php echo h($job['Job']['description']) ?>
		</td>
		<td>
			<dl>
				<?php echo '<dt>URL:</dt><dd>'.h($job['Job']['url']).'</dd>' ?>
				<?php echo '<dt>Default URI:</dt><dd>'.h($job['Job']['default_uri']).'</dd>' ?>
				<?php echo '<dt>Stylesheet:</dt><dd>'.h($job['Job']['stylesheet_uri_root']).'/'.h($job['Job']['stylesheet_uri']).'</dd>' ?>
				<?php
				if(!empty($job['Job']['css_mode'] > 1)){
					if(empty($job['Job']['preprocessor_uri'])){
						echo '<dt>Preprocessor:</dt><dd>'.$this->Html->image('dicon_warn_1.png',array('width' => '21px', 'height' => '21px')).' Preprocessor URI is undefined</dd>';
					} else {
						echo '<dt>Preprocessor:</dt><dd>'.h($job['Job']['preprocessor_uri_root']).'/'.h($job['Job']['preprocessor_uri']).'</dd>';
					}
				}
				echo '<dt>FTP account:</dt><dd>'.h($job['FtpAccount']['name']).'</dd>';
				?>
			</dl>
		</td>
		<!-- <td><?php echo h($job['FtpAccount']['name']) ?>&nbsp;</td> -->
		<td><?php echo h($cssModes[$job['Job']['css_mode']]) ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Copy'), ['action' => 'copy_row', 'Job', 'name', $job['Job']['id']]) ?>
			<?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $job['Job']['id']]) ?>
			<?php echo $this->Html->link(__('Build local'), ['action' => 'build_local', $job['Job']['id']], ['confirm' => __('Are you sure you want to build # %s?', $job['Job']['id'])]) ?>
			<?php echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $job['Job']['id']], ['confirm' => __('Are you sure you want to delete # %s?', $job['Job']['id'])]) ?>
		</td>
		<td><?php echo h($job['Job']['id']) ?>&nbsp;</td>
	</tr>
	
	<script>
	check_build(<?php echo $job['Job']['id'] ?>);
	</script>
	<?php endforeach; ?>
	</table>
	<p><?php $this->Paginator->counter() ?></p>
	<ul class="pagination">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'));
		echo $this->Paginator->numbers();
		echo $this->Paginator->next(__('next') . ' >');
	?>
	</ul>
</div>
<div class="actions">
	<h3><?php echo __('Actions') ?></h3>
	<?php echo $this->element('Navigation/side_nav') ?>
</div>


		