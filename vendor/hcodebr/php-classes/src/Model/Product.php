<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Product extends Model {
	public static function listAll() {
		$sql = new Sql();

		return $sql -> select("SELECT * FROM tb_products ORDER BY des_product");
	}

	public static function checkList($list) {
		foreach ($list as &$row) {
			$p = new Product();
			$p -> setData($row);
			$row = $p -> getValues();
		}

		return $list;
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

	public function get_From_URL($des_url) {
		$sql = new Sql();

		$rows = $sql -> select("SELECT * FROM tb_products WHERE des_url = :des_url LIMIT 1", array(
			":des_url" => $des_url,
		));

		$this -> setData($rows[0]);
	}

	public function Get_Categories() {
		$sql = new Sql();

		return $sql -> select("SELECT * FROM tb_categories a INNER JOIN tb_products_categories b ON a.id_category = b.id_category WHERE b.id_product = :id_product", array(
			":id_product" => $this -> getid_product(),
		));
	}

	public static function Get_Page($page = 1, $items_per_page = 10) {
		$start = ($page - 1) * $items_per_page;

		$sql = new Sql();

		$results = $sql -> select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products
			ORDER BY des_product
			LIMIT $start, $items_per_page;
		");

		$result_total = $sql -> select("SELECT FOUND_ROWS() AS nr_total;");

		return array(
			"data" => $results,
			"total" => (int)$result_total[0]["nr_total"],
			"pages" => ceil($result_total[0]["nr_total"] / $items_per_page),
		);
	}

	public static function Get_Page_Search($search, $page = 1, $items_per_page = 10) {
		$start = ($page - 1) * $items_per_page;

		$sql = new Sql();

		$results = $sql -> select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products
			WHERE des_product LIKE :search
			ORDER BY des_product
			LIMIT $start, $items_per_page;
		", array(
			":search" => "%".$search."%",
		));

		$result_total = $sql -> select("SELECT FOUND_ROWS() AS nr_total;");

		return array(
			"data" => $results,
			"total" => (int)$result_total[0]["nr_total"],
			"pages" => ceil($result_total[0]["nr_total"] / $items_per_page),
		);
	}
}

?>