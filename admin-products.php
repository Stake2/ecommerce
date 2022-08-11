<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function() {
	User::verifyLogin();

	$search = (isset($_GET["search"])) ? $_GET["search"] : "";
	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	if ($search != "") {
		$pagination = Product::Get_Page_Search($search);
	}

	else {
		$pagination = Product::Get_Page($page);
	}

	$pages = array();

	for ($i = 0; $i < $pagination["pages"]; $i++) {
		array_push($pages, array(
			"href" => "/admin/products?".http_build_query([
				"page" => $i + 1,
				"search" => $search,
			]),
			"text" => $i + 1,
			"active" => (($i + 1) == $page),
		));
	}

    $page = new PageAdmin();

	$page -> setTpl("products", array(
		"products" => $pagination["data"],
		"search" => $search,
		"pages" => $pages,
	));
});

$app->get("/admin/products/create", function() {
	User::verifyLogin();

	$user = new User();
	$user -> get($_SESSION[User::SESSION]["id_user"]);
	$opts = array("data" => ["user_name" => $user -> getdes_person()]);
    $page = new PageAdmin($opts);

	$page -> setTpl("products-create");
});

$app->post("/admin/products/create", function() {
	User::verifyLogin();

	$product = new Product();

	$product -> setData($_POST);

	$product -> save();

	header("Location: /admin/products");
	exit;
});

$app->get("/admin/products/:id_product", function($id_product) {
	User::verifyLogin();

	$product = new Product();

	$product -> get((int)$id_product);

	$product -> save();

    $page = new PageAdmin();

	$page -> setTpl("products-update", array(
		"product" => $product -> getValues(),
	));
});

$app->post("/admin/products/:id_product", function($id_product) {
	User::verifyLogin();

	$product = new Product();

	$product -> get((int)$id_product);

	$product -> setData($_POST);

	$product -> save();

	$product -> setPhoto($_FILES["file"]);

	header("Location: /admin/products");
	exit;
});

$app->get("/admin/products/:id_product/delete", function($id_product) {
	User::verifyLogin();

	$product = new Product();

	$product -> get((int)$id_product);

	$product -> delete();

	header("Location: /admin/products");
	exit;
});

?>