<?php
class Report {
	private $file;
	private $fileout;
	private $countLinks;
	private $destination = "";
	private $source = Array();
	private $listing = Array(); 
	public function __construct($filename){
		$this->parse($filename);
	}
	private function replace($str){
		return str_replace('"', '', $str);
	}
	private function outFile($filename){
		$fileinfo = pathinfo($filename);
		return $fileinfo['filename'].".txt";
;	}
	private function parse($filename){
		$this->fileout = $this->outFile($filename);
		$this->countLinks = isset($argv[2])?$argv[2]:50;
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
		$f = fopen($this->fileout, 'w');
		foreach ($this->listing as $key => $source) {
			fwrite($f, $key.PHP_EOL."на страницах".PHP_EOL);
			$i = 0;
			foreach ($source as $link) {
				$i++;
				fwrite($f, $link.PHP_EOL);
				fwrite($f, "и другие".PHP_EOL);
				if($i>$this->countLinks) break;
			}
			fwrite($f, PHP_EOL);
		}
		fclose($f);
	}
}

new Report($argv[1]);


?>