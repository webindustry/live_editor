<?php
App::uses('AppController', 'Controller');

class JobsController extends AppController {

	public function beforeFilter(){
		parent::beforeFilter();
		$cssModes = Configure::read('cssModes');
		$this->set(compact('cssModes'));
	}
	
	public function index() {
		$this->paginate = array(
			'contain' => array('FtpAccounts')
		);
		$this->set('jobs', $this->paginate($this->Job));
		
	}

	public function add() {
		//TODO strip http, https and www from url and trailing slash
		//TODO create uniqu id from planetplay.net 	planet-play-rochdale	bootstrap/css/custom.css
		//TODO
		/*
		 * project has many jobs
		 * project hasone owner
		 * 
		 */
		
		if ($this->request->is('post')) {
			$this->Job->create();
			if ($this->Job->save($this->request->data)) {
				$this->Session->setFlash(__('The job has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The job could not be saved. Please, try again.'));
			}
		}
		$ftpAccounts = $this->Job->FtpAccount->find('list');
		$this->set(compact('job', 'ftpAccounts'));
		
	}

	public function edit($id = null) {
		if (!$this->Job->exists($id)) {
			throw new NotFoundException(__('Invalid ftp account'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Job->save($this->request->data)) {
				$this->Session->setFlash(__('The job has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The job could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Job.' . $this->Job->primaryKey => $id));
			$this->request->data = $this->Job->find('first', $options);
		}
		$ftpAccounts = $this->Job->FtpAccount->find('list');
		$this->set(compact('ftpAccounts'));
	}

	public function delete($id = null) {
		$this->Job->id = $id;
		if (!$this->Job->exists()) {
			throw new NotFoundException(__('Invalid ftp account'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Job->delete()) {
			$this->Session->setFlash(__('The ftp account has been deleted.'));
		} else {
			$this->Session->setFlash(__('The ftp account could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}

	/* build_local method
	 *
	 * @param $jobId (int) id of the job
	 * @param $uri (string) optional uri which is appended to the url httrack command. must start with a forward slash
	 */
	public function build_local($jobId,$uri=false){
		
		//TODO if a uri is suppplied, first check to see if the site has already been mirrored
		//if not, issue a warning stating that the full site needs to be grabbed first
		//if the site has alreayd been mirrored then we can isue the single page command (although no asets will be downloaded for it)
		//also need some feedback as to the scripts progress, could do this will ob flush and ajax
		
		//TODO after build, preg replace all refs to httrack
		
		$this->autoRender = false;
		
		$job = $this->Job->findById($jobId);
		
		$storageDir = 'C:\wamp\www\live_editor'.DS.WEBROOT_DIR.DS.'cached_sites'.DS.$job['Job']['id'].DS.$job['Job']['url'];
		
		$mode = 1; //mode 0: grab individual page, mode 1: full mirror
		
		$uri = (!empty($job['Job']['default_uri'])?'/'.$job['Job']['default_uri']:'');
		
		if($mode == 1){
			$cmd = '"http://'.$job['Job']['url'].$uri.'" -O "'.$storageDir.'" "http://'.$job['Job']['url'].$uri.'" -%v';
			$cmd = Configure::read('httrack.path').' '.$cmd;
		}
		$this->exec_in_background($cmd);
		
		$this->Session->setFlash('The job build has been initiated.');
		return $this->redirect(['action' => 'index']);
		
	}
	
	public function poll_build($jobId){
		
		$this->autoRender = false;
		
		$job = $this->Job->findById($jobId);
		
		$storageDir = 'C:\wamp\www\live_editor'.DS.WEBROOT_DIR.DS.'cached_sites'.DS.$job['Job']['id'].DS.$job['Job']['url'];
		
		if(file_exists($storageDir.DS.'hts-in_progress.lock')){
			echo 1;
		} else {
			echo 0;
		}
		
	}
	
	public function get_build_index_page($jobId){
		
		$this->autoRender = false;
		
		$job = $this->Job->findById($jobId);
		
		$defaultUri = !empty($job['Job']['default_uri'])?str_replace('/',DS,$job['Job']['default_uri']).DS:'';
		$indexHtml = 'C:\wamp\www\live_editor'.DS.WEBROOT_DIR.DS.'cached_sites'.DS.$job['Job']['id'].DS.$job['Job']['url'].DS.$job['Job']['url'].DS.$defaultUri.'index.html';
		
		if(file_exists($indexHtml)){
			$lines = file($indexHtml);
			$uri = false;
			foreach($lines as $k => $v){
				if(strstr($v,'META HTTP-EQUIV="Refresh"')){
					$line = explode(' ',$v);
					foreach($line as $part){
						if(strstr($part,'URL')){
							$part = str_replace('URL=', '', $part);
							$part = preg_replace('/\".*/', '', $part);
							$uri = $part;
						}
					}
				}
			}
			if($uri){
				echo $uri;
			} else {
				echo 0;
			}
		} else {
			echo 0;
		}
		
	}
	
	public function exec_in_background($cmd) {
	    if (substr(php_uname(), 0, 7) == "Windows"){
	        pclose(popen("start /B ". $cmd, "r")); 
	    } else {
	        exec($cmd . " > /dev/null &");  
	    }
	}
	
}
