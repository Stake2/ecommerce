<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Product extends Model {
	public static function listAll() {
		$sql = new Sql();

		return $sql -> select("SELECT * FROM tb_products ORDER BY des_product");
	}

	public function save() {
		$sql = new Sql();

		$results = $sql -> select("CALL sp_products_save(:id_product, :des_product, :vl_price, :vl_width, :vl_height, :vl_length, :vl_weight, :des_url)", array(
			":id_product" => $this->getid_product(),
			":des_product" => $this->getdes_product(),
			":vl_price" => $this->getvl_price(),
			":vl_width" => $this->getvl_width(),
			":vl_height" => $this->getvl_height(),
			":vl_length" => $this->getvl_length(),
			":vl_weight" => $this->getvl_weight(),
			":des_url" => $this->getdes_url(),
		));

		$this -> setData($results[0]);
	}

	public function get($id_product) {
		$sql = new Sql();

		$results = $sql -> select("SELECT * FROM tb_products WHERE id_product = :id_product", array(
			":id_product" => $id_product,
		));

		$this -> setData($results[0]);
	}

	public function delete() {
		$sql = new Sql();

		$sql -> query("DELETE FROM tb_products WHERE id_product = :id_product", array(
			":id_product" => $this -> getid_product(),
		));
	}

	public function checkPhoto() {
		if (file_exists($_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR.$this -> getid_product().".jpg")) {
			$url = "/res/site/img/products/".$this -> getid_product().".jpg";
		}

		else {
			$url = "/res/site/img/product.jpg";
		}

		return $this -> setdes_photo($url);
	}

	public function getValues() {
		$this -> checkPhoto();

		$values = parent::getValues();

		return $values;
	}

	public function setPhoto($file) {
		$extension = explode(".", $file["name"]);
		$extension = end($extension);

		switch ($extension) {
			case "jpg";
			case "jpeg";
			$image = imagecreatefromjpeg($file["tmp_name"]);
			break;

			case "gif";
			$image = imagecreatefromgif($file["tmp_name"]);
			break;

			case "png";
			$image = imagecreatefrompng($file["tmp_name"]);
			break;
		}

		$destination = $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR.$this -> getid_product().".jpg";

		imagejpeg($image, $destination);

		imagedestroy($image);

		$this -> checkPhoto();
	}
}

?>