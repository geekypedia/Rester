<?php
	class EncodeDecodeSimple{
		function get_az_keys(){
			return "_abcdefghijklmnopqrstuvwxyz";
		}
		function get_random_number_az(){
			return rand(1,26);
		}
		public function encode($str){
			$r=$this->get_random_number_az();
			$key=$this->get_az_keys();
			$output_prefix=$key[$r];
			for($i=0;
			$i<$r;
			$i++){
				$c=$this->get_random_number_az();
				$output_prefix=$output_prefix.$key[$c];
			}
			$output=$output_prefix.base64_encode($str);
			return base64_encode($output);
		}
		public function decode($str){
			$str=base64_decode($str);
			$rc=$str[0];
			$key=$this->get_az_keys();
			$r=strpos($key,$rc);
			$output_candidate=substr($str,$r+1);
			$output=base64_decode($output_candidate);
			return $output;
		}
	}
	$encode_decode_simple=new EncodeDecodeSimple();
?>
