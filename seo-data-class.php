<?php


class seo_data_class {
	
	var $d=1;


	public function _construct(){	
		
		
	}
	
		
	
	public function zeo_add_post_meta($uniqueid, $value){
	
		return add_post_meta(get_the_ID(), $uniqueid, $value, true);
			
	}
	public function zeo_update_post_meta($uniqueid, $value){
		
		return update_post_meta(get_the_ID(), $uniqueid, $value);
	}
	public function zeo_delete_post_meta($uniqueid, $value){
		
		return delete_post_meta(get_the_ID(), $uniqueid, $value);
		
	}
	
	public function zeo_get_post_meta($uniqueid){		
		
		$meta_values = get_post_meta(get_the_ID(), $uniqueid, true);
		return $meta_values;
	}
	
	
	/* Addition Function for testing process */
	
	public function add($a,$b){
		
	return $a+$b;	
		
	}
	
	
}


?>