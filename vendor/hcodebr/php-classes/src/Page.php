<?php 

namespace Hcode;

use Rain\Tpl;
use \Hcode\Model\User;
use \Hcode\DB\Sql;

function Get_User_Name($is_admin = True) {
	$user = User::Get_From_Session();

	$sql = new Sql();

	$login = $user -> getdes_login();

	$results = $sql -> select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.id_person = b.id_person WHERE a.des_login = :LOGIN", array(
		":LOGIN" => $login,
	));

	if (count($results) > 0) {
		return $results[0]["des_person"];
	}

	else {
		return "";
	}
}

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

		$this -> user_name = Get_User_Name();

		if ($this -> options["header"] === True) {
			$this -> setTpl("header", array(
				"user_name" => $this -> user_name,
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