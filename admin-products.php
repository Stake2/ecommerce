<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function() {
	User::verifyLogin();

	$products = Product::listAll();

    $page = new PageAdmin();

	$page -> setTpl("products", array(
		"products" => $products,
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