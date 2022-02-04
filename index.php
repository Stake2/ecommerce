<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app = new \Slim\Slim();

$app->config("debug", true);

$app->get("/", function() {
    $page = new Page();

	$page -> setTpl("Index");
});

$app->get("/admin", function() {
	User::verifyLogin();

	$user = new User();
	$user -> get($_SESSION[User::SESSION]["id_user"]);
	$opts = array("data" => ["user_name" => $user -> getdes_person()]);
    $page = new PageAdmin($opts);

	$page -> setTpl("Index");
});

$app->get("/admin/login", function() {
    $page = new PageAdmin([
		"header" => False,
		"footer" => False,
	]);

	$page -> setTpl("Login");
});

$app->post("/admin/login", function() {
    User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;
});

$app->get("/admin/logout", function() {
    User::logout();

	header("Location: /admin/login");
	exit;
});

$app->get("/admin/users", function() {
	User::verifyLogin();

	$users = User::listAll();

	$user = new User();
	$user -> get($_SESSION[User::SESSION]["id_user"]);
	$opts = array("data" => ["user_name" => $user -> getdes_person()]);
    $page = new PageAdmin($opts);

	$page -> setTpl("users", array(
		"users" => $users,
	));
});

$app->get("/admin/users/create", function() {
	User::verifyLogin();

	$user = new User();
	$user -> get($_SESSION[User::SESSION]["id_user"]);
	$opts = array("data" => ["user_name" => $user -> getdes_person()]);
    $page = new PageAdmin($opts);

	$page -> setTpl("users-create");
});

$app->get("/admin/users/:id_user/delete", function($id_user) {
	User::verifyLogin();

	$user = new User();

	$user -> get((int)$id_user);

	$user -> delete();

	header("Location: /admin/users");
	exit;
});

$app->get("/admin/users/:id_user", function($id_user) {
	User::verifyLogin();

	$user = new User();

	$user -> get((int)$id_user);

	$user = new User();
	$user -> get($_SESSION[User::SESSION]["id_user"]);
	$opts = array("data" => ["user_name" => $user -> getdes_person()]);
    $page = new PageAdmin($opts);

	$page -> setTpl("users-update", array(
		"user" => $user -> getValues(),
	));
});

$app->post("/admin/users/create", function() {
	User::verifyLogin();

	$user = new User();

	$_POST["is_admin"] = (isset($_POST["is_admin"])) ? 1 : 0 ;

	$_POST["des_password"] = password_hash($_POST["des_password"], PASSWORD_DEFAULT, [
 		"cost"=>12
 	]);

	$user -> setData($_POST);

	$user -> save();

	header("Location: /admin/users");
	exit;
});

$app->post("/admin/users/:id_user", function($id_user) {
	User::verifyLogin();

	$user = new User();

	$_POST["is_admin"] = (isset($_POST["is_admin"])) ? 1 : 0 ;

	$user -> get((int)$id_user);

	$user -> setData($_POST);

	$user -> update();

	header("Location: /admin/users");
	exit;
});

$app->get("/admin/forgot", function() {
	$page = new PageAdmin([
		"header" => False,
		"footer" => False,
	]);

	$page -> setTpl("forgot");
});

$app->post("/admin/forgot", function() {
	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;
});

$app->get("/admin/forgot/sent", function() {
	$page = new PageAdmin([
		"header" => False,
		"footer" => False,
	]);

	$page -> setTpl("forgot-sent");
});

$app->get("/admin/forgot/reset", function() {
	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header" => False,
		"footer" => False,
	]);

	$page -> setTpl("forgot-reset", array(
		"name" => $user["des_person"],
		"code" => $_GET["code"],
	));
});

$app->post("/admin/forgot/reset", function() {
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["id_recovery"]);

	$user = new User();

	$user -> get((int)$forgot["id_user"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, array(
		"cost" => 12,
	));

	$user -> setPassword($password);

	$page = new PageAdmin([
		"header" => False,
		"footer" => False,
	]);

	$page -> setTpl("forgot-reset-success");
});

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

$app->get("/categories/:id_category", function($id_category) {
	$category = new Category();

	$category -> get((int)$id_category);

	$page = new Page();

	$page -> setTpl("category", array(
		"category" => $category -> getValues(),
		"products" => array(),
	));
});

$app->run();

 ?>