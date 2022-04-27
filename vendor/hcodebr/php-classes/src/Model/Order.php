<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\Cart;

class Order extends Model {
	const ERROR = "OrderError";
	const SUCCESS = "OrderSuccess";

	public function save() {
		$sql = new Sql();

		$results = $sql -> select("CALL sp_orders_save(:id_order, :id_cart, :id_user, :id_status, :id_address, :vl_total)", array(
			":id_order" => $this -> getid_order(),
			":id_cart" => $this -> getid_cart(),
			":id_user" => $this -> getid_user(),
			":id_status" => $this -> getid_status(),
			":id_address" => $this -> getid_address(),
			":vl_total" => $this -> getvl_total(),
		));

		if (count($results) > 0) {
			$this -> setData($results[0]);
		}
	}

	public function get(int $id_order) {
		$sql = new Sql();

		$results = $sql -> select("
		SELECT *
		FROM tb_orders a
		INNER JOIN tb_ordersstatus b USING(id_status)
		INNER JOIN tb_carts c USING(id_cart)
		INNER JOIN tb_users d ON d.id_user = a.id_user
		INNER JOIN tb_addresses e USING(id_address)
		INNER JOIN tb_persons f ON f.id_person = d.id_person
		WHERE a.id_order = :id_order
		",
		array(
			"id_order" => $id_order,
		));

		if (count($results) > 0) {
			$this -> setData($results[0]);
		}
	}

	public static function List_All() {
		$sql = new Sql();

		$results = $sql -> select("
		SELECT *
		FROM tb_orders a
		INNER JOIN tb_ordersstatus b USING(id_status)
		INNER JOIN tb_carts c USING(id_cart)
		INNER JOIN tb_users d ON d.id_user = a.id_user
		INNER JOIN tb_addresses e USING(id_address)
		INNER JOIN tb_persons f ON f.id_person = d.id_person
		ORDER BY a.dt_register DESC
		");

		if (count($results) > 0) {
			return $results;
		}
	}

	public function delete() {
		$sql = new Sql();

		$sql -> query("
		DELETE FROM tb_orders WHERE id_order = :id_order", array(
			":id_order" => $this -> getid_order(),
		));
	}

	public function Get_Cart():Cart {
		$cart = new Cart();

		$cart -> get((int)$this -> getid_cart());

		return $cart;
	}

	public static function Get_Message_Type($type) {
		$constants = array(
			"Success" => Order::SUCCESS,
			"Error" => Order::ERROR,
		);

		return $constants[$type];
	}

	public static function Set_Message($message, $type) {
		$_SESSION[Order::Get_Message_Type($type)] = $message;
	}

	public static function Get_Message($type) {

		$message = (isset($_SESSION[Order::Get_Message_Type($type)])) ? $_SESSION[Order::Get_Message_Type($type)] : "";

		Order::Clear_Message($type);

		return $message;
	}

	public static function Clear_Message($type) {
		$_SESSION[Order::Get_Message_Type($type)] = NULL;
	}
}

?>