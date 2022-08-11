<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Category extends Model {
	public static function listAll() {
		$sql = new Sql();

		return $sql -> select("SELECT * FROM tb_categories ORDER BY des_category");
	}

	public function save() {
		$sql = new Sql();

		$results = $sql -> select("CALL sp_categories_save(:id_category, :des_category)", array(
			":id_category" => $this->getid_category(),
			":des_category" => $this->getdes_category(),
		));

		$this -> setData($results[0]);

		Category::updateFile();
	}

	public function get($id_category) {
		$sql = new Sql();

		$results = $sql -> select("SELECT * FROM tb_categories WHERE id_category = :id_category", array(
			":id_category" => $id_category,
		));

		$this -> setData($results[0]);
	}

	public function delete() {
		$sql = new Sql();

		$sql -> query("DELETE FROM tb_categories WHERE id_category = :id_category", array(
			":id_category" => $this -> getid_category(),
		));

		Category::updateFile();
	}

	public static function updateFile() {
		$categories = Category::listAll();

		$html = array();

		foreach ($categories as $row) {
			$elements = '<li><a href="/categories/'.$row["id_category"].'">'.$row["des_category"].'</a></li>';

			if ($row != array_reverse($categories)[0]) {
				$elements .= "\n";
			}

			array_push($html, $elements);
		}

		file_put_contents($_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode("", $html));
	}

	public function getProducts($related = True) {
		$sql = new Sql();

		if ($related === True) {
			return $sql -> select("
			SELECT * FROM tb_products WHERE id_product IN(
				SELECT a.id_product
				FROM tb_products a
				INNER JOIN tb_products_categories b ON a.id_product = b.id_product
				WHERE b.id_category = :id_category
			);
			", array(
				"id_category" => $this -> getid_category(),
			));
		}

		else {
			return $sql -> select("
			SELECT * FROM tb_products WHERE id_product NOT IN(
				SELECT a.id_product
				FROM tb_products a
				INNER JOIN tb_products_categories b ON a.id_product = b.id_product
				WHERE b.id_category = :id_category
			);
			", array(
				"id_category" => $this -> getid_category(),
			));
		}
	}

	public function getProductsPage($page = 1, $items_per_page = 8) {
		$start = ($page - 1)*$items_per_page;

		$sql = new Sql();

		$results = $sql -> select("
		SELECT SQL_CALC_FOUND_ROWS *
		FROM tb_products a
		INNER JOIN tb_products_categories b ON a.id_product = b.id_product
		INNER JOIN tb_categories c ON c.id_category = b.id_category
		WHERE c.id_category = :id_category
		LIMIT $start, $items_per_page;
		", array(
			"id_category" => $this -> getid_category(),
		));

		$result_total = $sql -> select("
		SELECT FOUND_ROWS() AS nr_total;
		");

		return array(
			"data" => Product::checkList($results),
			"total" => (int)$result_total[0]["nr_total"],
			"pages" => ceil($result_total[0]["nr_total"] / $items_per_page),
		);
	}

	public function add_product(Product $product) {
		$sql = new Sql();

		$sql -> query("INSERT INTO tb_products_categories (id_category, id_product) VALUES(:id_category, :id_product)", array(
			":id_category" => $this -> getid_category(),
			":id_product" => $product -> getid_product(),
		));
	}

	public function remove_product(Product $product) {
		$sql = new Sql();

		$sql -> query("DELETE FROM tb_products_categories WHERE id_category = :id_category AND id_product = :id_product", array(
			":id_category" => $this -> getid_category(),
			":id_product" => $product -> getid_product(),
		));
	}

	public static function Get_Page($page = 1, $items_per_page = 10) {
		$start = ($page - 1) * $items_per_page;

		$sql = new Sql();

		$results = $sql -> select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories
			ORDER BY des_category
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
			FROM tb_categories
			WHERE des_category LIKE :search
			ORDER BY des_category
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