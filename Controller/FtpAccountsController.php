<?php
App::uses('AppController', 'Controller');

class FtpAccountsController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$this->FtpAccount->recursive = 0;
		$this->set('ftpAccounts', $this->Paginator->paginate());
	}

	public function view($id = null) {
		if (!$this->FtpAccount->exists($id)) {
			throw new NotFoundException(__('Invalid ftp account'));
		}
		$options = array('conditions' => array('FtpAccount.' . $this->FtpAccount->primaryKey => $id));
		$this->set('ftpAccount', $this->FtpAccount->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->FtpAccount->create();
			if ($this->FtpAccount->save($this->request->data)) {
				$this->Session->setFlash(__('The ftp account has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The ftp account could not be saved. Please, try again.'));
			}
		}
	}

	public function edit($id = null) {
		if (!$this->FtpAccount->exists($id)) {
			throw new NotFoundException(__('Invalid ftp account'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->FtpAccount->save($this->request->data)) {
				$this->Session->setFlash(__('The ftp account has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The ftp account could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('FtpAccount.' . $this->FtpAccount->primaryKey => $id));
			$this->request->data = $this->FtpAccount->find('first', $options);
		}
	}

	public function delete($id = null) {
		$this->FtpAccount->id = $id;
		if (!$this->FtpAccount->exists()) {
			throw new NotFoundException(__('Invalid ftp account'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->FtpAccount->delete()) {
			$this->Session->setFlash(__('The ftp account has been deleted.'));
		} else {
			$this->Session->setFlash(__('The ftp account could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
	
	public function test_ftp($id = null){

		$this->autoRender = false;
		
		$this->FtpAccount->id = $id;
		if (!$this->FtpAccount->exists()) {
			throw new NotFoundException(__('Invalid ftp account'));
		}
		
		$options = array('conditions' => array('FtpAccount.' . $this->FtpAccount->primaryKey => $id));
		$ftpAccount = $this->FtpAccount->find('first', $options);
		
		$conn_id = @ftp_connect($ftpAccount['ftp_host']);
		$login_result = @ftp_login($conn_id, $ftpAccount['ftp_user'], $ftpAccount['ftp_pass']);
		ftp_pasv($conn_id, true);
		if ((!$conn_id)) {
			echo 'FTP CONNECTION FAILED';
		} else {
			@ftp_close($conn_id);
			echo 'FTP CONNECTION OK';
		}
	}
	
}
