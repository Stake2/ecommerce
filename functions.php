<?php 

use \Hcode\Model\User;
use \Hcode\DB\Sql;

function formatPrice($vl_price) {
	if (!$vl_price > 0) $vl_price = 0;

	return number_format($vl_price, 2, ",", ".");
}

function checkLogin($is_admin = True) {
	return User::Check_Login($is_admin);
}

function Get_User_Name($is_admin = True) {
	$user = User::Get_From_Session();

	$sql = new Sql();

	$login = $user -> getdes_login();

	$results = $sql -> select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.id_person = b.id_person WHERE a.des_login = :LOGIN", array(
		":LOGIN" => $login,
	));

	return $results[0]["des_person"];
}

?>