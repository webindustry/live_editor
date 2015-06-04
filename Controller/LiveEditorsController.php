<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class LiveEditorsController extends AppController {
	
	public $components = array('Cakeless.Cakeless');

	public $uses = ['Job'];
	
	public function index($jobId=0,$mode=1) {
		
		$this->layout = 'live_editor';
		
		$jobs = Set::combine( $this->Job->find('all'),'{n}.Job.id','{n}.Job' );
		$this->set(compact('jobs','jobId','mode'));
	}
	
	public function data_state_manager($filename,$mode='w'){
		
		$this->autoRender = false;

		$settings = [
			'dir' => CACHE.'live_editor'.DS,
			'prefix' => '',
			'extension' => 'json'
		];
		
		$dir = new Folder($settings['dir'], true, 0755);
		$file = new File($settings['dir'].$settings['prefix'].$filename.'.'.$settings['extension'], true, 0644);
		
		if($mode == 'w'){
			$file->write($this->request->input(),'w+');
			$data = $this->request->input();
		} elseif($mode == 'r') {
			$data = $file->read();
		} elseif($mode == 'd') {
			$file->delete();
			foreach($_COOKIE as $k => $v){
				if(strstr($k,'LiveEditor')){
					unset($_COOKIE[$k]);
				}
			}
			$data = '{"success":300}';
		}
		$file->close();
		$data = (!empty($data)) ? $data : '{"error":100}';
		echo $data;
				
	}

	public function backup_manager($jobId){
		
		$this->autoRender = false;
		
		$this->loadModel('Jobs');
		$job = $this->Job->findById($jobId);
		
		$storageDir = CACHE.'live_editor'.DS.'backups'.DS.$jobId.DS;
		
		$dir = $this->stylesheet_path($job,'stylesheet_uri');
		$pathinfo = pathinfo($dir);
		$jobDir = $storageDir . $pathinfo['dirname'] . DS;
		$filenameCss = $pathinfo['basename'];
		$filenameScss = $pathinfo['filename'].'.scss';
		$filenameLess = $pathinfo['filename'].'.less';
		
		$dir = new Folder($storageDir);
		$dirContents = $dir->read(true);
		$dirs = array();
		foreach($dirContents[0] as $k => $v){
			$dirs[$k]['key'] = $v;
			$dirs[$k]['str'] = date('l jS \of F Y h:i:s A', $v);
		}
		$dirs = array_reverse($dirs);
		
		return json_encode($dirs);

		
		$fileCss = new File($jobDir.$filenameCss, true, 0644);
		$fileCss->src_uri = $job['Job']['stylesheet_uri'];
		
		if(empty($job['Job']['preprocessor_uri'])){
			
			$fileScss = new File($jobDir.$filenameScss, true, 0644);
			$fileLess = new File($jobDir.$filenameLess, true, 0644);
			$pathinfo = pathinfo($job['Job']['stylesheet_uri']);
			$fileScss->src_uri = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.scss';
			$fileLess->src_uri = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.less';
			
		} else {
			
			$dir = $this->stylesheet_path($job,'preprocessor_uri');
			$pathinfo = pathinfo($dir);
			$jobDir = $storageDir.$pathinfo['dirname'] . DS;
			$filenameCss = $pathinfo['basename'];
			$filenameScss = $pathinfo['filename'].'.scss';
			$filenameLess = $pathinfo['filename'].'.less';
			
			$fileScss = new File($jobDir.$filenameScss, true, 0644);
			$fileLess = new File($jobDir.$filenameLess, true, 0644);
			$fileScss->src_uri = $job['Job']['preprocessor_uri'];
			$fileLess->src_uri = $job['Job']['preprocessor_uri'];
		}
		$out = json_encode('--------');
		
		$fileCss->close();
		$fileScss->close();
		$fileLess->close();
		$out = (!empty($out)) ? $out : '{"error":100}';
		echo $out;
		
	}
	
	public function css_progress_manager($jobId,$mode='w',$ftp=false){
		
		$this->autoRender = false;
		
		$this->loadModel('Jobs');
		$job = $this->Job->findById($jobId);
		
		$storageDir = CACHE.'live_editor'.DS.'css_progress'.DS.$jobId.DS;
	
		$content = json_decode($this->request->input());

		$dir = $this->stylesheet_path($job,'stylesheet_uri');
		$pathinfo = pathinfo($dir);
		$jobDir = $storageDir . $pathinfo['dirname'] . DS;
		$filenameCss = $pathinfo['basename'];
		$filenameScss = $pathinfo['filename'].'.scss';
		$filenameLess = $pathinfo['filename'].'.less';
		
		new Folder($jobDir, true, 0755);
		$fileCss = new File($jobDir.$filenameCss, true, 0644);
		$fileCss->src_uri = $job['Job']['stylesheet_uri'];
		
		if(empty($job['Job']['preprocessor_uri'])){
			
			$fileScss = new File($jobDir.$filenameScss, true, 0644);
			$fileLess = new File($jobDir.$filenameLess, true, 0644);
			$pathinfo = pathinfo($job['Job']['stylesheet_uri']);
			$fileScss->src_uri = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.scss';
			$fileLess->src_uri = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.less';
			
		} else {
			
			$dir = $this->stylesheet_path($job,'preprocessor_uri');
			$pathinfo = pathinfo($dir);
			$jobDir = $storageDir.$pathinfo['dirname'] . DS;
			$filenameCss = $pathinfo['basename'];
			$filenameScss = $pathinfo['filename'].'.scss';
			$filenameLess = $pathinfo['filename'].'.less';
			
			$fileScss = new File($jobDir.$filenameScss, true, 0644);
			$fileLess = new File($jobDir.$filenameLess, true, 0644);
			$fileScss->src_uri = $job['Job']['preprocessor_uri'];
			$fileLess->src_uri = $job['Job']['preprocessor_uri'];
		}
		

		if($mode == 'w'){
			if($job['Job']['css_mode'] == 1){
				$fileCss->write($content,'w+');
			} elseif($job['Job']['css_mode'] == 2){
				
				$uri = Configure::read('APP_BASE_URI').'scss.processor.php';
				$rawCss = $this->post_file_get_contents($uri,$content);
				$fileCss->write($rawCss,'w+');
				$fileScss->write($content,'w+');
			} elseif($job['Job']['css_mode'] == 3){
				$uri = Configure::read('APP_BASE_URI').'less.processor.php';
				$rawCss = $this->post_file_get_contents($uri,$content);
				$fileCss->write($rawCss,'w+');
				$fileLess->write($content,'w+');
			}
			$out = $content;
			if($ftp){
				//TODO reqirk this awful funciton. ftp mthod should be supplied an array of files and open and close conn afetra ll files not for each one individually!!
				if(empty($job['FtpAccount'])){
					$out = '{"error":102; "message":"Invalid FTP account"}';
				} else {
					if($job['Job']['css_mode'] == 1){
						
						if($this->publish_ftp($job['FtpAccount'],$fileCss)){
							$out = '{"success":400; "message":"FTP success"}';
						} else {
							$out = '{"error":101; "message":"FTP failure"}';
						}
						
					} elseif($job['Job']['css_mode'] == 2 || $job['Job']['css_mode'] == 3){

						if($this->publish_ftp($job['FtpAccount'],$fileCss)){
							$msg[] = '{"success":400; "message":"FTP success"}';
						} else {
							$msg[] = '{"error":101; "message":"FTP failure"}';
						}
						
						if($job['Job']['css_mode'] == 2){
							if($this->publish_ftp($job['FtpAccount'],$fileScss)){
								$msg[] = '{"success":400; "message":"FTP success (SCSS)"}';
							} else {
								$msg[] = '{"error":101; "message":"FTP failure (SCSS)"}';
							}
						}
						
						if($job['Job']['css_mode'] == 3){
							if($this->publish_ftp($job['FtpAccount'],$fileLess)){
								$msg[] = '{"success":400; "message":"FTP success (LESS)"}';
							} else {
								$msg[] = '{"error":101; "message":"FTP failure (LESS)"}';
							}
						}
						
						$out = implode(',',$msg);
						
					}
					
				}
			}
			
		} elseif($mode == 'r') {
			
			if($job['Job']['css_mode'] == 1){
				$out = $fileCss->read();
			} elseif($job['Job']['css_mode'] == 2){
				$out = $fileScss->read();
			} elseif($job['Job']['css_mode'] == 3){
				$out = $fileLess->read();
			}
			
		} elseif($mode == 'd') {
			$fileCss->delete();
			$out = '{"success":300}';
		}
		$fileCss->close();
		$fileScss->close();
		$fileLess->close();
		$out = (!empty($out)) ? $out : '{"error":100}';
		echo $out;
		
	}
	
	public function css_backup_manager($jobId,$mode='w',$key=null){
		
		$this->autoRender = false;
		
		$this->loadModel('Jobs');
		$job = $this->Job->findById($jobId);
		
		$storageDir = CACHE.'live_editor'.DS.'backups'.DS.$jobId;
		
		//list of all backups available for this job
		$dir = new Folder($storageDir);
		$dirContents = $dir->read(true);
		$dirs = array();
		foreach($dirContents[0] as $k => $v){
			$dirs[$k]['key'] = $v;
			$dirs[$k]['str'] = date('l jS \of F Y h:i:s A', $v);
		}
		$dirs = array_reverse($dirs);

		if($mode == 'r' && empty($key)){
			//if read mode is supplied but no key then load the most recent backup
			if(!empty($dirs[0])){
				$key = $dirs[0]['key'];
			} else {
				return '{"error":1001}';
			}
		} elseif($mode == 'd' && empty($key)){
			return '{"error":1002}';
		} elseif($mode == 'w' && empty($key)){
			$key = time();
		}
		$storageDir = $storageDir.DS.$key.DS;
	
		//$content = '';
		//if($mode == 'w'){
		//	$content = json_decode($this->request->input());
		//} elseif($mode == 'r'){
		//	$content = '';
		//}
		
		$content = json_decode($this->request->input());
		
		$dir = $this->stylesheet_path($job,'stylesheet_uri');
		$pathinfo = pathinfo($dir);
		$jobDir = $storageDir . $pathinfo['dirname'] . DS;
		$filenameCss = $pathinfo['basename'];
		$filenameScss = $pathinfo['filename'].'.scss';
		$filenameLess = $pathinfo['filename'].'.less';
		
		new Folder($jobDir, true, 0755);
		$fileCss = new File($jobDir.$filenameCss, true, 0644);
		$fileCss->src_uri = $job['Job']['stylesheet_uri'];
		
		if(empty($job['Job']['preprocessor_uri'])){
			
			$fileScss = new File($jobDir.$filenameScss, true, 0644);
			$fileLess = new File($jobDir.$filenameLess, true, 0644);
			$pathinfo = pathinfo($job['Job']['stylesheet_uri']);
			$fileScss->src_uri = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.scss';
			$fileLess->src_uri = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.less';
			
		} else {
			
			$dir = $this->stylesheet_path($job,'preprocessor_uri');
			$pathinfo = pathinfo($dir);
			$jobDir = $storageDir.$pathinfo['dirname'] . DS;
			$filenameCss = $pathinfo['basename'];
			$filenameScss = $pathinfo['filename'].'.scss';
			$filenameLess = $pathinfo['filename'].'.less';
			
			$fileScss = new File($jobDir.$filenameScss, true, 0644);
			$fileLess = new File($jobDir.$filenameLess, true, 0644);
			$fileScss->src_uri = $job['Job']['preprocessor_uri'];
			$fileLess->src_uri = $job['Job']['preprocessor_uri'];
		}

		if($mode == 'w'){
			if($job['Job']['css_mode'] == 1){
				$fileCss->write($content,'w+');
			} elseif($job['Job']['css_mode'] == 2){
				$uri = Configure::read('APP_BASE_URI').'scss.processor.php';
				$rawCss = $this->post_file_get_contents($uri,$content);
				$fileCss->write($rawCss,'w+');
				$fileScss->write($content,'w+');
			} elseif($job['Job']['css_mode'] == 3){
				$uri = Configure::read('APP_BASE_URI').'less.processor.php';
				$rawCss = $this->post_file_get_contents($uri,$content);
				$fileCss->write($rawCss,'w+');
				$fileLess->write($content,'w+');
			}
			$out = $content;
			
		} elseif($mode == 'r') {

			if($job['Job']['css_mode'] == 1){
				$out = $fileCss->read();
			} elseif($job['Job']['css_mode'] == 2){
				$out = $fileScss->read();
			} elseif($job['Job']['css_mode'] == 3){
				$out = $fileLess->read();
			}

		} elseif($mode == 'd') {
			$fileCss->delete();
			$out = '{"success":300}';
		}
		$fileCss->close();
		$fileScss->close();
		$fileLess->close();
		$out = (!empty($out)) ? $out : '{"error":100}';
		echo $out;
		
	}
	
	public function stylesheet_path($job,$dir){
		
		$path = $job['Job']['url'] . DS . $job['Job'][$dir];
		$path = str_replace('/', DS, $path);
		
		return $path;
		
	}
	
	public function scrape_job_style_data($jobId=null){
		
		$this->autoRender = false;
		
		$job = $this->Job->findById($jobId);
		if(!empty($job)){			
			$stylesheetPath = $job['Job']['stylesheet_uri_root'].'/'.$job['Job']['stylesheet_uri'];
			$preprocessorPath = $job['Job']['preprocessor_uri_root'].'/'.$job['Job']['preprocessor_uri'];
			$stylesheetData = @file_get_contents($stylesheetPath);
			$data['stylesheet_data'] = $stylesheetData ? $stylesheetData : array("error" => 111, "message" => "Stylesheet file could not be found: $stylesheetPath");
			if($job['Job']['css_mode'] > 1){
				$preprocessor_data = @file_get_contents($preprocessorPath);
				$data['preprocessor_data'] = $preprocessor_data ? $preprocessor_data : array("error" => 111, "message" => "Preprocessor file could not be found: $preprocessorPath");
			} else {
				$data['preprocessor_data'] = '';
			}
			echo json_encode($data);
		}
		
	}
	
	public function check_uri(){
		
		$this->autoRender = false;
		
		$uri = $this->request->input();
		//$uri = Configure::read('APP_BASE_URI').'http://127.0.0.1test_site_1b.htm';
		$c = curl_init();
		//curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($c, CURLOPT_URL, $uri);
	    //curl_setopt($c, CURLOPT_USERPWD, 'crepple' . ":" . 'breals');
	    curl_setopt($c, CURLOPT_HEADER, 1);
	    curl_setopt($c, CURLOPT_FILETIME, true);
        curl_setopt($c, CURLOPT_NOBODY, true);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	    $header = curl_exec($c);
	    $status  = curl_getinfo($c,CURLINFO_HTTP_CODE);
	    //pr($uri);
		//pr(json_encode($header));
	    //pr($status);
	    curl_close($c);
	    
	    $header = preg_replace(["/\r\n/","/\r/","/\n/"],'<br />',$header);
	    if ($status >= 400/* || $status == 0*/) {
	    	echo '{"error":100, "http_response_code":'.$status.', "header" : '.json_encode($header).'}';
	    } else {
	    	echo '{"success":100, "http_response_code":'.$status.', "header" : '.json_encode($header).'}';
	    }
		
	}
	
	public function publish_ftp($ftpAccount,$file){

		$fileFtp['src'] = $file->path;
		$fileFtp['dst'] = $ftpAccount['ftp_path'].$file->src_uri;
		
		$conn_id = @ftp_connect($ftpAccount['ftp_host']);
		$login_result = @ftp_login($conn_id, $ftpAccount['ftp_user'], $ftpAccount['ftp_pass']);
		ftp_pasv($conn_id, true);
		if ((!$conn_id)) {
			return false;
		} else {
			//pr($fileFtp);
			$upload = @ftp_put($conn_id, $fileFtp['dst'], $fileFtp['src'], FTP_BINARY);
			@ftp_close($conn_id);
			if(!$upload){
				return false;
			}
			return true;
		}
	}
	
	public function post_file_get_contents($uri,$data){
		
		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => $data
		    )
		);
		
		$context  = stream_context_create($opts);
		
		return file_get_contents($uri, false, $context);
		
	}

	public function extract_assets($jobId=null){

		$this->autoRender = false;
		
		$this->loadModel('Jobs');
		$job = $this->Job->findById($jobId);
		
		$fileCss = $job['Job']['stylesheet_uri_root'].'/'.$job['Job']['stylesheet_uri']; 
		if(empty($job['Job']['preprocessor_uri'])){
			$pathinfo = pathinfo($job['Job']['stylesheet_uri']);
			$fileScss = 'http://' . $job['Job']['url'] . '/' . $pathinfo['dirname'].'/'.$pathinfo['filename'].'.scss';
			$fileLess = 'http://' . $job['Job']['url'] . '/' . $pathinfo['dirname'].'/'.$pathinfo['filename'].'.less';
		} else {
			$fileScss = $job['Job']['preprocessor_uri_root'].'/'.$job['Job']['preprocessor_uri'];
			$fileLess = $job['Job']['preprocessor_uri_root'].'/'.$job['Job']['preprocessor_uri'];
		}
		
		
		//CSS
		$dataCss = file_get_contents($fileCss);
		
		//HEX
		$assets['hex'] = array();
		preg_match_all('/#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?\b/', $dataCss, $matches);
		if(!empty($matches[0])){
			foreach($matches[0] as $k => $v){
				$assets['hex'][] = strtoupper($v);
			}
		}
		
		//RGB
		$assets['rgb'] = array();
		preg_match_all("/rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i", $dataCss, $matches);
		if(!empty($matches[0])){
			foreach($matches[0] as $k => $v){
				$assets['rgb'][] = preg_replace("/\s/",'',strtolower($v));
			}
		}
		
		//RGBA
		preg_match_all("/rgba\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\,\s*([0-9(.{1})]+)\s*\)/i", $dataCss, $matches);
		if(!empty($matches[0])){
			foreach($matches[0] as $k => $v){
				$assets['rgb'][] = preg_replace("/\s/",'',strtolower($v));
			}
		}
		
	//TODO parse @import files
	//TODO this entire function could do with parsing the codemirror contents rather than the live files	
		//scss/less
		$vars = array();
		if($job['Job']['css_mode'] == 2){
			$dataScss = file_get_contents($fileScss);
			$dataScss = $this->compress_code($dataScss);
			$dataScss = explode(';',$dataScss);
			foreach($dataScss as $k => $v){
				if($v[0] == '$'){
					$v = trim($v);
					$var = explode(':',$v);
					if(!empty($var[1])){
						$var[0] = trim($var[0]);
						$var[1] = trim($var[1]);
						$vars[] = $var;
					}
				}
			}
		} elseif($job['Job']['css_mode'] == 3){
			$dataLess = file_get_contents($fileLess);
			$dataLess = $this->compress_code($dataLess);
			$dataLess = explode(';',$dataLess);
			foreach($dataLess as $k => $v){
				if($v[0] == '@'){
					$v = trim($v);
					$var = explode(':',$v);
					if(!empty($var[1])){
						$var[0] = trim($var[0]);
						$var[1] = trim($var[1]);

						//if($var[1][0] == '@'){
						//	$var[1] = !empty($varlib[$var[1]])?$varlib[$var[1]]:'';
						//} else {
						//	$varlib[$var[0]] = $var[1];
						//}
						$vars[] = $var;
					}
				}
			}
		}
		//pr($varlib);
		$assets['hex'] = array_unique($assets['hex']);
		$assets['rgb'] = array_unique($assets['rgb']);
		$assets['variables'] = $vars;

		if(!empty($assets)){
			return json_encode($assets);
		}
		return '{"error":109,"message":"No colours found"}';

	}
	
	public function compress_code($code=null){
	
		if(!empty($code)){
			$code = preg_replace("/\n/","",$code);
			$code = preg_replace("/\r/","",$code);
		}
		
		return $code;
	
	}
	
	public function help(){
		
		$this->autoRender = false;

		echo '
		<div id="help" class="doc">
			
			<h3>CSS mode</h3>
			<p>When editing a standard CSS file, changes are instant. When editing LESS/SCSS code, you need to save the
			the code to see the changes</p>
			
				
			<h3>Keyboard Shortcuts</h3>
			<dl>
				<dt>Move selected lines up</dt>
				<dd>
					<span class="key">ALT</span>
					<span class="keyPlus">+</span>
					<span class="key">Up</span>
				</dd>
				<dt>Move selected lines down</dt>
				<dd>
					<span class="key">ALT</span>
					<span class="keyPlus">+</span>
					<span class="key">Down</span>
				</dd>
				<dt>Copy selected lines up</dt>
				<dd>
					<span class="key">ALTGR</span>
					<span class="keyPlus">+</span>
					<span class="key">Up</span>
				</dd>
				<dt>Copy selected lines down</dt>
				<dd>
					<span class="key">ALTGR</span>
					<span class="keyPlus">+</span>
					<span class="key">Down</span>
				</dd>
				<dt>Delete selected lines</dt>
				<dd>
					<span class="key">CTRL</span>
					<span class="keyPlus">+</span>
					<span class="key">D</span>
				</dd>
				<dt>Toggle comments</dt>
				<dd>
					<span class="key">CTRL</span>
					<span class="keyPlus">+</span>
					<span class="key">.</span>
				</dd>
				<dt>Start searching</dt>
				<dd>
					<span class="key">CTRL</span>
					<span class="keyPlus">+</span>
					<span class="key">F</span> / <span class="key">CMD</span>
					<span class="keyPlus">+</span>
					<span class="key">F</span>
				</dd>
				<dt>Find next</dt>
				<dd>
					<span class="key">CTRL</span>
					<span class="keyPlus">+</span>
					<span class="key">G</span> / <span class="key">CMD</span>
					<span class="keyPlus">+</span>
					<span class="key">G</span>
				</dd>
				<dt>Find previous</dt>
				<dd>
					<span class="key">CTRL</span>
					<span class="keyPlus">+</span>
					<span class="key">SHIFT</span>
					<span class="keyPlus">+</span>
					<span class="key">G</span> / <span class="key">CMD</span>
					<span class="keyPlus">+</span>
					<span class="key">SHIFT</span>
					<span class="keyPlus">+</span>
					<span class="key">G</span>
				</dd>
				<dt>Replace</dt>
				<dd>
					<span class="key">CTRL</span>
					<span class="keyPlus">+</span>
					<span class="key">SHIFT</span>
					<span class="keyPlus">+</span>
					<span class="key">F</span> / <span class="key">CMD</span>
					<span class="keyPlus">+</span>
					<span class="key">OPTION</span>
					<span class="keyPlus">+</span>
					<span class="key">F</span>
				</dd>
				<dt>Replace all</dt>
				<dd>
					<span class="key">CTRL</span>
					<span class="keyPlus">+</span>
					<span class="key">SHIFT</span>
					<span class="keyPlus">+</span>
					<span class="key">R</span> / <span class="key">CMD</span>
					<span class="keyPlus">+</span>
					<span class="key">SHIFT</span>
					<span class="keyPlus">+</span>
					<span class="key">OPTION</span>
					<span class="keyPlus">+</span>
					<span class="key">F</span>
				</dd>
				<dt>Toggle inspector</dt>
				<dd>
					<span class="key">ALT</span>
					<span class="keyPlus">+</span>
					<span class="key">SHIFT</span>
					<span class="keyPlus">+</span>
					<span class="key">G</span>
				</dd>
				<dt>Toggle editor visibility</dt>
				<dd>
					<span class="key">ALT</span>
					<span class="keyPlus">+</span>
					<span class="key">SHIFT</span>
					<span class="keyPlus">+</span>
					<span class="key">R</span>
				</dd>
				<dt>Autocomplete hints</dt>
				<dd>
					<span class="key">CTRL</span>
					<span class="keyPlus">+</span>
					<span class="key">SPACE</span> / <span class="key">CMD</span>
					<span class="keyPlus">+</span>
					<span class="key">SPACE</span>
				</dd>
			</dl>
		</div>	
		';
		
		die();
		
	}

}
