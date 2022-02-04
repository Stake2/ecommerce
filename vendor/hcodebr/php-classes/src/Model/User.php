<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {
	const SESSION = "User";
	const KEY = "HcodePHP7_Secret";

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

	public static function verifyLogin($is_admin = True) {
		if (!isset($_SESSION[User::SESSION]) or !$_SESSION[User::SESSION] or !(int)$_SESSION[User::SESSION]["id_user"] > 0 or (bool)$_SESSION[User::SESSION]["is_admin"] !== $is_admin) {
			header("Location: /admin/login");
			exit;
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
			":des_person" => $this->getdes_person(),
			":des_login" => $this->getdes_login(),
			":des_password" => $this->getdes_password(),
			":des_email" => $this->getdes_email(),
			":nr_phone" => $this->getnr_phone(),
			":is_admin" => $this->getis_admin(),
		));

		$this -> setData($results[0]);
	}

	public function get($id_user) {
		$sql = new Sql();

		$results = $sql -> select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(id_person) WHERE a.id_user = :id_user", array(
			":id_user" => $id_user,
		));

		$this -> setData($results[0]);
	}

	public function update() {
		$sql = new Sql();

		$results = $sql -> select("CALL sp_usersupdate_save(:id_user, :des_person, :des_login, :des_password, :des_email, :nr_phone, :is_admin)", array(
			":id_user" => $this->getid_user(),
			":des_person" => $this->getdes_person(),
			":des_login" => $this->getdes_login(),
			":des_password" => $this->getdes_password(),
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

	private function encrypt_decrypt($action, $string) {
		$key = User::KEY;
		$output = false;
		$cipher = 'AES-256-CBC';
				
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

	public static function getForgot($email) {
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
				$code = User::encrypt_decrypt("encrypt", $data_recovery["id_recovery"]);
				
				/*
				$key = User::KEY;
				$original_text = $data_recovery["id_recovery"];
				echo $original_text;
				$ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
				$iv = openssl_random_pseudo_bytes($ivlen);
				$ciphertext_raw = openssl_encrypt($original_text, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
				$hmac = hash_hmac("sha256", $ciphertext_raw, $key, $as_binary = true);
				$code = base64_encode($iv.$hmac.$ciphertext_raw);
				*/

				$link = "http://www.hcodecommerce.com.br:8080/admin/forgot/reset?code=$code";

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
		$id_recovery = User::encrypt_decrypt("decrypt", $code);

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
}

?>