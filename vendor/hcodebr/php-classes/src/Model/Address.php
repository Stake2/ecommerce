<?php 

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;

class Address extends Model {
	const SESSION_ERROR = "AddressError";

	public static function Get_CEP($numero_cep) {
		$numero_cep = str_replace("-", "", $numero_cep);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "http://viacep.com.br/ws/$numero_cep/json/");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, False);

		$data = json_decode(curl_exec($ch), True);

		curl_close($ch);

		return $data;
	}

	public function Load_From_CEP($numero_cep) {
		$data = Address::Get_CEP($numero_cep);

		if (isset($data["logradouro"]) == True and $data["logradouro"] != "") {
			$this -> setdes_address($data["logradouro"]);
			$this -> setdes_complement($data["complemento"]);
			$this -> setdes_district($data["bairro"]);
			$this -> setdes_city($data["localidade"]);
			$this -> setdes_state($data["uf"]);
			$this -> setdes_country("Brasil");
			$this -> setdes_zip_code($numero_cep);
		}
	}

	public function save() {
		$sql = new Sql();

		$results = $sql -> select("CALL sp_addresses_save(:id_address, :id_person, :des_address, :des_number, :des_complement, :des_city, :des_state, :des_country, :des_zip_code, :des_district)", array(
			":id_address" => $this -> getid_address(),
			":id_person" => $this -> getid_person(),
			":des_address" => utf8_encode($this -> getdes_address()),
			":des_number" => utf8_encode($this -> getdes_number()),
			":des_complement" => utf8_encode($this -> getdes_complement()),
			":des_city" => utf8_encode($this -> getdes_city()),
			":des_state" => utf8_encode($this -> getdes_state()),
			":des_country" => utf8_encode($this -> getdes_country()),
			":des_zip_code" => $this -> getdes_zip_code(),
			":des_district" => $this -> getdes_district(),
		));

		if (count($results) > 0) {
			$this -> setData($results[0]);	
		}
	}

	public static function Set_Error($message) {
		$_SESSION[Address::SESSION_ERROR] = $message;
	}

	public static function Get_Error() {
		$message = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";

		Address::Clear_Error();

		return $message;
	}

	public static function Clear_Error() {
		$_SESSION[Address::SESSION_ERROR] = NULL;
	}
}

?>