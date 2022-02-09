<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\User;

class Cart extends Model {
	const SESSION = "Cart";

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
}

?>