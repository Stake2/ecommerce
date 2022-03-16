<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\User;

class Cart extends Model {
	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";

	public static function Get_From_Session() {
		$cart = new Cart();

		if (isset($_SESSION[Cart::SESSION]) and (int)$_SESSION[Cart::SESSION]["id_cart"] > 0) {
			$cart -> get((int)$_SESSION[Cart::SESSION]["id_cart"]);
		}

		else {
			$cart -> Get_From_Session_ID();

			if (!(int)$cart -> getid_cart() > 0) {
				$data = array(
					"des_session_id" => session_id(),
				);

				if (User::Check_Login(False)) {
					$user = User::Get_From_Session();

					$data["id_user"] = $user -> getid_user();
				}

				$cart -> setData($data);

				$cart -> save();

				$cart -> Set_To_Session();
			}
		}

		return $cart;
	}

	public function Set_To_Session() {
		$_SESSION[Cart::SESSION] = $this -> getValues();
	}

	public function get(int $id_cart) {
		$sql = new Sql();

		$results = $sql -> select("SELECT * FROM tb_carts WHERE id_cart = :id_cart", array(
			"id_cart" => $id_cart,
		));

		if (count($results) > 0) {
			$this -> setData($results[0]);
		}
	}

	public function Get_From_Session_ID() {
		$sql = new Sql();

		$results = $sql -> select("SELECT * FROM tb_carts WHERE des_session_id = :des_session_id", array(
			"des_session_id" => session_id(),
		));

		if (count($results) > 0) {
			$this -> setData($results[0]);
		}
	}

	public function save() {
		$sql = new Sql();

		$results = $sql -> select("CALL sp_carts_save(:id_cart, :des_session_id, :id_user, :des_zip_code, :vl_freight, :nr_days)", array(
			":id_cart" => $this -> getid_cart(),
			":des_session_id" => $this -> getdes_session_id(),
			":id_user" => $this -> getid_user(),
			":des_zip_code" => $this -> getdes_zip_code(),
			":vl_freight" => $this -> getvl_freight(),
			":nr_days" => $this -> getnr_days(),
		));

		$this -> setData($results[0]);
	}

	public function Get_Products() {
		$sql = new Sql();

		$rows = $sql -> select("
			SELECT b.id_product, b.des_product, b.vl_price, b.vl_width, b.vl_height, b.vl_length, b.vl_weight, b.des_url, COUNT(*) AS nr_quantity, SUM(b.vl_price) AS total_value
			FROM tb_cartsproducts a
			INNER JOIN tb_products b ON a.id_product = b.id_product
			WHERE a.id_cart = :id_cart AND a.dt_removed IS NULL
			GROUP BY b.id_product, b.des_product, b.vl_price, b.vl_width, b.vl_height, b.vl_length, b.vl_weight, b.des_url
			ORDER BY b.des_product
		", array(
			":id_cart" => $this -> getid_cart(),
		));

		return Product::checkList($rows);
	}

	public function Get_Product_Totals() {
		$sql = new Sql();

		$results = $sql -> select("
		SELECT
		SUM(vl_price) AS vl_price, SUM(vl_width) AS vl_width,
		SUM(vl_height) AS vl_height, SUM(vl_length) AS vl_length,
		SUM(vl_weight) AS vl_weight, COUNT(*) as nr_quantity
		FROM tb_products a
		INNER JOIN tb_cartsproducts b ON a.id_product = b.id_product
		WHERE b.id_cart = :id_cart AND dt_removed IS NULL;", array(
			":id_cart" => $this -> getid_cart(),
		));

		if (count($results) > 0) {
			return $results[0];
		}

		else {
			return array();
		}
	}

	public function Add_Product(Product $product) {
		$sql = new Sql();

		$sql -> query("INSERT INTO tb_cartsproducts (id_cart, id_product) VALUES(:id_cart, :id_product)", array(
			":id_cart" => $this -> getid_cart(),
			":id_product" => $product -> getid_product(),
		));

		$this -> Get_Calculate_Total();
	}

	public function Remove_Product(Product $product, $all = False) {
		$sql = new Sql();

		if ($all === True) {
			$sql -> query("UPDATE tb_cartsproducts SET dt_removed = NOW() WHERE id_cart = :id_cart AND id_product = :id_product AND dt_removed IS NULL", array(
				":id_cart" => $this -> getid_cart(),
				":id_product" => $product -> getid_product(),
			));
		}

		else {
			$sql -> query("UPDATE tb_cartsproducts SET dt_removed = NOW() WHERE id_cart = :id_cart AND id_product = :id_product AND dt_removed IS NULL LIMIT 1", array(
				":id_cart" => $this -> getid_cart(),
				":id_product" => $product -> getid_product(),
			));
		}

		$this -> Get_Calculate_Total();
	}

	public function Set_Freight($zip_code) {
		$zip_code = str_replace("-", "", $zip_code);

		$totals = $this -> Get_Product_Totals();

		if ($totals["nr_quantity"] > 0) {
			if ($totals["vl_height"] < 2) $totals["vl_height"] = 3;
			if ($totals["vl_length"] < 16) $totals["vl_length"] = 17;
			if ($totals["vl_width"] < 11) $totals["vl_width"] = 12;

			$query_string = http_build_query(array(
				"nCdEmpresa" => "",
				"sDsSenha" => "",
				"nCdServico" => "40010",
				"sCepOrigem" => "09853120",
				"sCepDestino" => $zip_code,
				"nVlPeso" => $totals["vl_weight"],
				"nCdFormato" => "1",
				"nVlComprimento" => $totals["vl_length"],
				"nVlAltura" => $totals["vl_height"],
				"nVlLargura" => $totals["vl_width"],
				"nVlDiametro" => "0",
				"sCdMaoPropria" => "S",
				"nVlValorDeclarado" => $totals["vl_price"],
				"sCdAvisoRecebimento" => "S",
			));

			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$query_string);

			$result = $xml -> Servicos -> cServico;

			if ($result -> MsgErro != "") {
				Cart::Set_Message_Error($result -> MsgErro);
			}

			else {
				Cart::Clear_Message_Error();
			}

			$this -> setnr_days($result -> PrazoEntrega);
			$this -> setvl_freight(Cart::Format_Value_To_Decimal($result -> Valor));
			$this -> setdes_zip_code($zip_code);
			$this -> save();

			return $result;
		}

		else {

		}
	}

	public static function Format_Value_To_Decimal($value):float {
		$value = str_replace(".", "", $value);

		return str_replace(",", ".", $value);
	}

	public static function Set_Error($message) {
		$_SESSION[Cart::SESSION_ERROR] = $message;
	}

	public static function Get_Error() {
		$message = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

		Cart::Clear_Error();

		return $message;
	}

	public static function Clear_Error() {
		$_SESSION[Cart::SESSION_ERROR] = NULL;
	}

	public function Update_Freight() {
		if ($this -> getdes_zip_code() != "") {
			$this -> Set_Freight($this -> getdes_zip_code());
		}
	}

	public function getValues() {
		$this -> Get_Calculate_Total();

		return parent::getValues();
	}

	public function Get_Calculate_Total() {
		$this -> Update_Freight();

		$totals = $this -> Get_Product_Totals();

		$this -> setvl_subtotal($totals["vl_price"]);
		$this -> setvl_total($totals["vl_price"] + $this -> getvl_freight());

		return $totals;
	}
}

?>