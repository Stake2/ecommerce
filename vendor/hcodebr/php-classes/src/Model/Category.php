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
}

?>