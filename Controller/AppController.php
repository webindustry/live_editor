<?php

App::uses('Controller', 'Controller');

class AppController extends Controller {
	
	public $components = array(
		'Session','RequestHandler','Paginator',
		'DebugKit.Toolbar', //DebugKit
	);

	public $helpers = array(
			
		'Session',
		'Html' => array('className' => 'BoostCake.BoostCakeHtml'),
		'Form' => array('className' => 'BoostCake.BoostCakeForm'),
		'Paginator' => array('className' => 'BoostCake.BoostCakePaginator'),
		'BootstrapCustom',
	);
	
	public function beforeRender(){
		
		Configure::write('APP_BASE_URI','http://'.$_SERVER['HTTP_HOST'].$this->webroot);
		
		//print_r(Configure::read('APP_BASE_URI'));exit;
		
		$jsVars = '<script>'."\n";
		$jsVars .= 'var HTTP_HOST = "'.$_SERVER['HTTP_HOST'].'";'."\n";
		$jsVars .= 'var WEBROOT = "'.$this->webroot.'";'."\n";
		$jsVars .= 'var APP_BASE_URI = "'.Configure::read('APP_BASE_URI').'";'."\n";
		if(Configure::read('debug') > 0){
			$jsVars .= 'var debuggery = true;'."\n";
		} else {
			$jsVars .= 'var debuggery = false;'."\n";
		}
		$jsVars .= '</script>'."\n";
		
		$this->set(compact('jsVars'));
		
	}
	
	//Copy table row
	public function copy_row($model=null,$identField='name',$id=null){
	
		if(!empty($model)){
			$modelHuman = Inflector::humanize($model);
	
			$this->$model->id = $id;
			if (!$this->$model->exists()) {
				throw new NotFoundException(__('Invalid '.$modelHuman));
			}
			$modelData = $this->$model->read();
			$data[$model] = $modelData[$model];
			$data[$model][$identField] = $data[$model][$identField].' (copy)';
			unset($data[$model]['id']);
	
			$this->$model->create();
			if ($this->$model->save($data[$model])) {
				$this->Session->setFlash(__('The '.$modelHuman.' has been copied'));
			} else {
				$this->Session->setFlash(__('The '.$modelHuman.' could not be copied. Please, try again'));
			}
			return $this->redirect(array('action' => 'index'));
		
		} else {
			
			$this->Session->setFlash(__('No model data supplied. Nothing was copied'));
			return $this->redirect(array('action' => 'index'));
		
		}
		
	}
	
}
