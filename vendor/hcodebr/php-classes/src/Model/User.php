<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model {
	const SESSION = "User";

	public static function login($login, $password) {
		$sql = new Sql();

		$results = $sql -> select("SELECT * from tb_users WHERE des_login = :LOGIN", array(
			":LOGIN" => $login,
		));

		if (count($results) === 0) {
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $results[0];

		if (password_verify($password, $data["des_password"]) === True) {
			$user = new User();

			$user -> setData($data);

			$_SESSION[User::SESSION] = $user -> getValues();

			return $user;
		}

		else {
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}
	}

	public static function verifyLogin($inadmin = True) {
		if (!isset($_SESSION[User::SESSION]) or !$_SESSION[User::SESSION] or !(int)$_SESSION[User::SESSION]["id_user"] > 0 or (bool)$_SESSION[User::SESSION]["is_admin"] !== $inadmin) {
			header("Location: /admin/login");
			exit;
		}
	}

	public static function logout() {
	$_SESSION[User::SESSION] = NULL;
	}
}

?>