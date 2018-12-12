<?php
//$file = fopen('./server_error_(5xx)_inlinks.csv', 'r');


class Report {
	private $file;
	private $destination = "";
	private $source = Array();
	private $listing = Array(); 
	public function __construct($filename){
		$this->parse($filename);
	}
	private function replace($str){
		return str_replace('"', '', $str);
	}
	private function parse($filename){
		$file = fopen($filename, 'r');
		while(!feof($file)){			
			$buffer = fgets($file, 4096);
		    $table = explode(',', $buffer);
		    if(isset($table[1]) && isset($table[2])){
			    $source = $this->replace($table[1]);
			    $destination = $this->replace($table[2]);
				if(empty($table[2]) || $destination=="Destination") continue;
			    if($this->destination==""){
			    	$this->step($destination, $source);
			    }elseif($this->destination!=$this->replace($table[2])){
			    	$this->setBlock();
			    	$this->step($destination, $source);
			    }else{
			    	$this->source[] = $source;
			    } 
		    }
		}
		$this->setBlock();
		fclose($file);
		$this->printListing();
	}
	private function setBlock(){
		$this->listing = array_merge($this->listing, Array($this->destination => $this->source));
		$this->source = Array();
	}
	private function step($dest, $source){
		$this->destination = $dest;
		array_push($this->source, $source);
	}
	private function setDestination($destination){
		$this->destination = $destination;
	}

	private function printListing(){
		foreach ($this->listing as $key => $source) {
			echo $key.PHP_EOL."на страницах".PHP_EOL;
			foreach ($source as $link) {
				echo $link.PHP_EOL;
			}
			echo PHP_EOL;
		}
	}
}

new Report('./server_error_(5xx)_inlinks.csv');


?>