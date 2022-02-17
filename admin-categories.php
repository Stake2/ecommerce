<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

$app->get("/admin/categories", function() {
	User::verifyLogin();

	$categories = Category::listAll();

    $page = new PageAdmin();

	$page -> setTpl("categories", array(
		"categories" => $categories,
	));
});

$app->get("/admin/categories/create", function() {
	User::verifyLogin();

    $page = new PageAdmin();

	$page -> setTpl("categories-create");
});

$app->post("/admin/categories/create", function() {
	User::verifyLogin();

	$category = new Category();

	$category -> setData($_POST);

	$category -> save();

	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:id_category/delete", function($id_category) {
	User::verifyLogin();

	$category = new Category();

	$category -> get((int)$id_category);

	$category -> delete();

	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:id_category", function($id_category) {
	User::verifyLogin();

	$category = new Category();

	$category -> get((int)$id_category);

    $page = new PageAdmin();

	$page -> setTpl("categories-update", array(
		"category" => $category -> getValues(),
	));
});

$app->post("/admin/categories/:id_category", function($id_category) {
	User::verifyLogin();

	$category = new Category();

	$category -> get((int)$id_category);

	$category -> setData($_POST);

	$category -> save();

	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:id_category/products", function($id_category) {
	User::verifyLogin();

	$category = new Category();

	$category -> get((int)$id_category);

    $page = new PageAdmin();

	$page -> setTpl("categories-products", array(
		"category" => $category -> getValues(),
		"products_related" => $category -> getProducts(),
		"products_not_related" => $category -> getProducts(False),
	));
});

$app->get("/admin/categories/:id_category/products/:id_product/add", function($id_category, $id_product) {
	User::verifyLogin();

	$category = new Category();

	$category -> get((int)$id_category);

	$product = new Product();

	$product -> get((int)$id_product);

	$category -> add_product($product);

	header("Location: /admin/categories/".$id_category."/products");
	exit;
});

$app->get("/admin/categories/:id_category/products/:id_product/remove", function($id_category, $id_product) {
	User::verifyLogin();

	$category = new Category();

	$category -> get((int)$id_category);

	$product = new Product();

	$product -> get((int)$id_product);

	$category -> remove_product($product);

	header("Location: /admin/categories/".$id_category."/products");
	exit;
});

?>