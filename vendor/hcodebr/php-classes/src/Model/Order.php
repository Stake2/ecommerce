<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Order extends Model {
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
}

?>