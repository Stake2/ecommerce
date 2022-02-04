<?php 

namespace Hcode;

use Rain\Tpl;

class Page {
	private $tpl;
	private $options = [];
	private $defaults = [
		"header" => True,
		"footer" => True,
		"data" => [],
	];
	private $user_name;

	public function __construct($opts = array(), $tpl_dir = "/views/") {
		$this -> options = array_merge($this -> defaults, $opts);

		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug"         => False, // set to false to improve the speed
		);

		Tpl::configure($config);

		$this -> tpl = new Tpl;

		$this -> setData($this -> options["data"]);

		if ($this -> options["header"] === True) {
			$this -> setTpl("header", array(
				"user_name", $this -> user_name,
			));
		}
	}

	private function setData($data = array()) {
		foreach ($data as $key => $value) { 
			$this -> tpl -> assign($key, $value);
		}
	}

	public function setTpl($name, $data = array(), $returnHTML = False) {
		$this -> setData($data);
		return $this -> tpl -> draw($name, $returnHTML);
	}

	public function __destruct() {
		if ($this -> options["header"] === True) {
			$this -> tpl -> draw("footer");
			
		};
	}
}

 ?>