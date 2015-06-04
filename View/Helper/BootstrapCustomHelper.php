<?php 
class BootstrapCustomHelper extends AppHelper {
	
	public $helpers = array('Form');
	
	public function input_group($fields,$clear=false){
		
		$output = '';	
		$output .= '<div class="input-group">';
		foreach($fields as $field){
			$output .= $field;
		}
		$output .= '<span class="input-group-btn">';
		$output .= '<button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search"></span></button>';
		$clearCheck = '';
		if($clear){
			$uid = uniqid();
			$clearCheck = $this->Form->input(false, array('name' => 'clearSearch', 'type' => 'checkbox', 'class' => 'clearSearch hidden', 'rel' => $uid, 'div' => false, 'label' => false));
			$output .= '<button class="btn btn-default clearSearch" type="submit" value="clearSearch" rel="'.$uid.'" title="Clear search"><span class="glyphicon glyphicon-remove"></span></button>';
		}
		$output .= '</span>';
		$output .= '</div>';
		$output .= $clearCheck;
		
		return $output;
		
	}
	
}

?>