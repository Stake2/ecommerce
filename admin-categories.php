<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app->get("/admin/categories", function() {
	User::verifyLogin();

	$categories = Category::listAll();

	$user = new User();
	$user -> get($_SESSION[User::SESSION]["id_user"]);
	$opts = array("data" => ["user_name" => $user -> getdes_person()]);
    $page = new PageAdmin($opts);

	$page -> setTpl("categories", array(
		"categories" => $categories,
	));
});

$app->get("/admin/categories/create", function() {
	User::verifyLogin();

	$user = new User();
	$user -> get($_SESSION[User::SESSION]["id_user"]);
	$opts = array("data" => ["user_name" => $user -> getdes_person()]);
    $page = new PageAdmin($opts);

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

	$user = new User();
	$user -> get($_SESSION[User::SESSION]["id_user"]);
	$opts = array("data" => ["user_name" => $user -> getdes_person()]);
    $page = new PageAdmin($opts);

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

?>