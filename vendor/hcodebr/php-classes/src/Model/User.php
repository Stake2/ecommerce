<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {
	const SESSION = "User";
	const KEY = "HcodePHP7_Secret";
	const ERROR = "UserError";
	const REGISTER = "UserRegister";
	const SUCCESS = "UserSuccess";

	public static function login($login, $password) {
		$sql = new Sql();

		$results = $sql -> select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(id_person) WHERE a.des_login = :LOGIN", array(
			":LOGIN" => $login,
		));

		if (count($results) === 0) {
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $results[0];

		echo $data["des_password"];
		echo "<br>";
		echo User::encrypt_decrypt("encrypt", User::KEY, $password);

		if (password_verify($password, $data["des_password"]) === True) {
			$user = new User();

			$data["des_person"] = utf8_encode($data["des_person"]);

			$user -> setData($data);

			$_SESSION[User::SESSION] = $user -> getValues();

			return $user;
		}

		else {
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}
	}

	public static function verifyLogin($is_admin = True) {
		$user = new User();

		if (User::Check_Login($is_admin) === False) {
			if ($is_admin == True) {
				header("Location: /admin/login");
			}

			else {
				header("Location: /login");
			}

			exit;
		}
	}

	public static function Check_Login($is_admin = True) {
		if (!isset($_SESSION[User::SESSION]) or !$_SESSION[User::SESSION] or !(int)$_SESSION[User::SESSION]["id_user"] > 0) {
			# Não está logado
			return False;
		}

		else {
			if ($is_admin == True and (bool)$_SESSION[User::SESSION]["is_admin"] === True) {
				return True;
			}

			else if ($is_admin === False) {
				return True;
			}

			else {
				return False;
			}
		}
	}

	public static function logout() {
		$_SESSION[User::SESSION] = NULL;
	}

	public static function listAll() {
		$sql = new Sql();

		return $sql -> select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(id_person) ORDER BY b.des_person");
	}

	public function getLogged() {
		$sql = new Sql();

		$results = $sql -> select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(id_person) WHERE a.id_user = :id_user", array(
			":id_user" => $this->getid_user(),
		));

		return $results[0];
	}

	public function save() {
		$sql = new Sql();

		$results = $sql -> select("CALL sp_users_save(:des_person, :des_login, :des_password, :des_email, :nr_phone, :is_admin)", array(
			":des_person" => utf8_decode($this -> getdes_person()),
			":des_login" => $this -> getdes_login(),
			":des_password" => User::encrypt_decrypt("encrypt", User::KEY, $this -> getdes_password()),
			":des_email" => $this -> getdes_email(),
			":nr_phone" => $this -> getnr_phone(),
			":is_admin" => $this -> getis_admin(),
		));

		$this -> setData($results[0]);
	}

	public static function Get_From_Session() {
		$user = new User();

		if (isset($_SESSION[User::SESSION]) and (int)$_SESSION[User::SESSION]["id_user"] > 0) {
			$user -> setData($_SESSION[User::SESSION]);
		}

		return $user;
	}

	public function get($id_user) {
		$sql = new Sql();

		$results = $sql -> select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(id_person) WHERE a.id_user = :id_user", array(
			":id_user" => $id_user,
		));

		echo $id_user;

		$data = $results[0];

		$data["des_person"] = utf8_encode($data["des_person"]);

		$this -> setData($data);
	}

	public function update($change_password = True) {
		if ($change_password == True) {
		   $password = User::encrypt_decrypt("encrypt", User::KEY, $this -> getdes_password());
		}

		else {	 
		   $password = $_POST["des_password"];
		}

		$sql = new Sql();

		$results = $sql -> select("CALL sp_usersupdate_save(:id_user, :des_person, :des_login, :des_password, :des_email, :nr_phone, :is_admin)", array(
			":id_user" => $this->getid_user(),
			":des_person" => utf8_decode($this->getdes_person()),
			":des_login" => $this->getdes_login(),
			":des_password" => $password,
			":des_email" => $this->getdes_email(),
			":nr_phone" => $this->getnr_phone(),
			":is_admin" => $this->getis_admin(),
		));

		$this -> setData($results[0]);
	}

	public function delete() {
		$sql = new Sql();

		$sql -> query("CALL sp_users_delete(:id_user)", array(
			":id_user" => $this -> getid_user(),
		));
	}

	private static function encrypt_decrypt($action, $key, $string) {
		$output = False;
		$cipher = "AES-256-CBC";
				
		if ($action === "encrypt") {
			$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
			$output = base64_encode(openssl_encrypt($string, $cipher, $key, 0, $iv));

			return $output."::".bin2hex($iv);
		}

		else if ($action === "decrypt") {
			$string = explode("::", $string);
			$toDecode = base64_decode($string[0]);
			$iv = hex2bin($string[1]);
			$output = openssl_decrypt($toDecode, $cipher, $key, 0, $iv);

			return $output;
		}
	}

	public static function getForgot($email, $is_admin = True) {
		$sql = new Sql();

		$results = $sql -> select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(id_person) WHERE a.des_email = :email;", array(
			":email" => $email,
		));

		if (count($results) == 0) {
			throw new \Exception("Não foi possível recuperar a senha.");
		}

		else {
			$data = $results[0];

			$results_2 = $sql -> select("CALL sp_userspasswordsrecoveries_create(:id_user, :des_ip)", array(
				":id_user" => $data["id_user"],
				":des_ip" => $_SERVER["REMOTE_ADDR"],
			));

			if (count($results_2) == 0) {
				throw new \Exception("Não foi possível recuperar a senha.");
			}

			else {
				$data_recovery = $results_2[0];

				# Encrypt with key and original text
				$code = User::encrypt_decrypt("encrypt", User::KEY, $data_recovery["id_recovery"]);

				$link = "http://www.hcodecommerce.com.br:8080/".(($is_admin == True) ? "admin/" : "")."forgot/reset?code=$code";

				$mailer = new \Hcode\Mailer($data["des_email"], $data["des_person"], "Redefinir senha da Hcode Store", "forgot", array(
					"name" => $data["des_person"],
					"link" => $link,
				));

				$mailer -> send();

				return $data;
			}
		}
	}

	public static function validForgotDecrypt($code) {
		# Descriptografar
		$id_recovery = User::encrypt_decrypt("decrypt", User::KEY, $code);

		$sql = new Sql();

		$results = $sql -> select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(id_user)
			INNER JOIN tb_persons c USING(id_person)
			WHERE 
				a.id_recovery = :id_recovery
				AND
				a.dt_recovery IS NULL
				AND
				DATE_ADD(a.dt_register, INTERVAL 1 HOUR) >= NOW();
		",
		array(
			":id_recovery" => $id_recovery,
		));

		if (count($results) == 0) {
			throw new \Exception("Não foi possível recuperar a senha.");
		}

		else {
			return $results[0];
		}
	}

	public static function setForgotUsed($id_recovery) {
		$sql = new Sql();

		$sql -> query("UPDATE tb_userspasswordsrecoveries SET dt_recovery = NOW() WHERE id_recovery = :id_recovery", array(
			":id_recovery" => $id_recovery,
		));
	}

	public function setPassword($password) {
		$sql = new Sql();

		$sql -> query("UPDATE tb_users SET des_password = :password WHERE id_user = :id_user", array(
			":password" => $password,
			":id_user" => $this -> getid_user(),
		));
	}

	public static function Check_If_Login_Exists($login) {
		$sql = new Sql();

		$results = $sql -> select("SELECT * FROM tb_users WHERE des_login = :des_login", array(
			":des_login" => $login,
		));

		return (count($results) > 0);
	}

	public function Get_Orders() {
		$sql = new Sql();

		$results = $sql -> select("
		SELECT *
		FROM tb_orders a
		INNER JOIN tb_ordersstatus b USING(id_status)
		INNER JOIN tb_carts c USING(id_cart)
		INNER JOIN tb_users d ON d.id_user = a.id_user
		INNER JOIN tb_addresses e USING(id_address)
		INNER JOIN tb_persons f ON f.id_person = d.id_person
		WHERE a.id_user = :id_user
		",
		array(
			"id_user" => $this -> getid_user(),
		));

		return $results;
	}

	public static function Get_Message_Type($type) {
		$constants = array(
			"Success" => User::SUCCESS,
			"Error" => User::ERROR,
			"Register" => User::REGISTER,
		);

		return $constants[$type];
	}

	public static function Set_Message($message, $type) {
		$_SESSION[User::Get_Message_Type($type)] = $message;
	}

	public static function Get_Message($type) {

		$message = (isset($_SESSION[User::Get_Message_Type($type)])) ? $_SESSION[User::Get_Message_Type($type)] : "";

		User::Clear_Message($type);

		return $message;
	}

	public static function Clear_Message($type) {
		$_SESSION[User::Get_Message_Type($type)] = NULL;
	}
}

?>