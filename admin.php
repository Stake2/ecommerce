<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get("/admin", function() {
	User::verifyLogin();

    $page = new PageAdmin();

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
	$_POST["password"] = User::encrypt_decrypt("encrypt", User::KEY, $_POST["password"]);

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;
});

$app->get("/admin/logout", function() {
    User::logout();

	header("Location: /admin/login");
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

?>