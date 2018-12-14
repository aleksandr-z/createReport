<?php

class Report {
	private $file;
	private $path;
	private $options = Array();
	private $arrFiles = Array();
	private $fileout;
	private $countLinks;
	private $destination = "";
	private $source = Array();
	private $listing = Array(); 
	public function __construct($argv){
		$this->options = Array(
			'path' => __DIR__,
			'count' => 20,
			'text' => 'Найдено на страницах:',
		);
		for($i=1; $i<count($argv); $i++){
			$opt = explode("=", $argv[$i]);
			if(array_key_exists($opt[0], $this->options)){
				$this->options[$opt[0]] = $opt[1];
			}
			
		}

		var_dump($this->options);
		// if(!isset($this->options['path']) || $this->options['path']==''){
		// 	$this->options['path'] = __DIR__;
		// }

		$this->options['path'] = $this->checkPath($this->options['path']);
		if(isset($this->options['file']) && $this->options['file']==''){
			if(file_exists($this->options['path'].$this->options['file'])){
				$this->parse($this->options['path'].$this->options['file']);
			}else{
				throw new Excepiton('file not found');
			}
		}else{
			$this->getFiles();
			foreach ($this->arrFiles as $file) {
				//echo $this->options['path'].$file;
				$this->parse($this->options['path'].$file);
			}	
		}
	}
	private function checkPath($file){
		return substr($file, -1)==DIRECTORY_SEPARATOR?$file:$file.DIRECTORY_SEPARATOR;
	}
	private function getFiles(){
		if (isset($this->options['file']) && $this->options['file']!=''){
			return  $this->options['file'];
		}elseif(file_exists($this->options['path'])){
			$files = scandir($this->options['path']);
			foreach ($files as $key => $value) {
				$fileinfo = pathinfo($value);
				var_dump($fileinfo);
				if($fileinfo['extension']=="csv") array_push($this->arrFiles, $value);
			}
		}else{
			throw new Exception('dir is not found');
		}
	}
	private function replace($str){
		return str_replace('"', '', $str);
	}
	private function outFile($filename){
		$fileinfo = pathinfo($filename);
		return $fileinfo['filename'].".txt";
	}
	private function parse($filename){
		$this->fileout = $this->outFile($filename);
		$this->countLinks = isset($this->options['count'])?$this->options['count']:50;
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
			fwrite($f, $key.PHP_EOL.$this->options['text'].PHP_EOL);
			$i = 1;
			foreach ($source as $link) {
				$i++;
				fwrite($f, $link.PHP_EOL);
				if($i>$this->countLinks) {
					fwrite($f, "и другие".PHP_EOL);
					break;
				}
			}
			fwrite($f, PHP_EOL);
		}
		fclose($f);
	}
}

new Report($argv);


?>