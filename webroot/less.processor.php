<?php 

	require_once '../Config/config.php';

	require_once FS_ROOT . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'leafo' . DIRECTORY_SEPARATOR . 'lessphp' . DIRECTORY_SEPARATOR . 'lessc.inc.php';

	$data = file_get_contents('php://input');
	parse_str($data,$dataArr);
	
	if(empty($dataArr['code'])){
		$dataArr = array();
		$dataArr['code'] = $data;
	}

	$input = build_import_data($dataArr);
	if(is_array($input)){
		
		if($input['error']){
			echo json_encode( array('error' => 106, 'message' => $input['message']) );
		}
		
	} else {
		
		$compiler = new lessc();
		try {
			echo $compiler->compile($input);
		} catch (Exception $e) {
			echo json_encode( array('error' => 105, 'message' => 'LESS parse error: ' . $e->getMessage()) );
		}

	}

	function build_import_data($dataArr){
		
		//Attempt to merge any @import files
		//We cannot use the preprocessors setImportDir function because we are working remotely
		//TODO if an absolute url is not specified then prepend the less files path to the @import file reference
		
		$input = $dataArr['code'];
		$dirname = '';
		if(!empty($dataArr['path'])){
			$pathinfo = pathinfo($dataArr['path']);
			$dirname = $pathinfo['dirname'] . '/';
		}
		
		$imports = preg_match_all("/\@import.*\n/", $input, $matches);
		$importedData = '';

		if(is_array($matches[0])){
			
			$input = preg_replace("/\@import.*\n/","",$input);
			
			foreach($matches[0] as $k => $v){
				if(!strstr($v,'http://') && !strstr($v,'https://')){
					$v = str_replace("@import ","",$v);
					$v = str_replace("'","",$v);
					$v = str_replace('"','',$v);
					$v = str_replace(';','',$v);
					$v = str_replace('(','',$v);
					$v = str_replace(')','',$v);
					$v = preg_replace(array("/\r/","/\n/"),'',$v);
					$pathinfo = pathinfo($v);
					if(empty($pathinfo['extension']) || $pathinfo['extension'] == 'less'){
						$path = $dirname . $v;
						$uriCheck = check_uri($path);
						if(!empty($uriCheck['error'])){
							return $uriCheck;//die($uriCheck['json']);//TODO Make sure this error message is visible!!
						} else {
							$importedData .= curl_import_data($path) . "\n\n";
						}
					}
				}
			}
			
		}
		return $importedData.$input;
		
	}

	function check_uri($uri){
		
		$c = curl_init();
	    curl_setopt($c, CURLOPT_URL, $uri);
	    curl_setopt($c, CURLOPT_HEADER, 1);
	    curl_setopt($c, CURLOPT_FILETIME, true);
        curl_setopt($c, CURLOPT_NOBODY, true);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	    $header = curl_exec($c);
	    $status  = curl_getinfo($c,CURLINFO_HTTP_CODE);
	    curl_close($c);
	    $headerItems = explode("\n",$header);
	    $location = $uri;
	    foreach($headerItems as $k => $v){
	    	if(strstr($v,'Location: ')){
	    		$location = str_replace('Location: ','',$v);
	    	}
	    }
	    $diverted = false;
	    if($location != $uri){
	    	$diverted = true;
	    }
	    $header = preg_replace(["/\r\n/","/\r/","/\n/"],'<br />',$header);
	    if ($status >= 400/* || $status == 0*/ || $diverted == true) {
	    	return array('error' => true, "message" => "Failed to import $uri", "header" => json_encode($header));
	    } else {
	    	return true;
	    }
		
	}
	
	function curl_import_data($uri){
		
		$content = '';
		$c = curl_init();
	    curl_setopt($c, CURLOPT_URL, $uri);
	    curl_setopt($c, CURLOPT_HEADER, 0);
	    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	    $content = curl_exec($c);
	    curl_close($c);
	    //echo '<pre>';
	    //	print_r($content);
	    //echo '</pre>';
	    $content;
		
		return $content;
		
	}

?>