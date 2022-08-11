<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get("/admin/users", function() {
	User::verifyLogin();

	$search = (isset($_GET["search"])) ? $_GET["search"] : "";
	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	if ($search != "") {
		$pagination = User::Get_Page_Search($search);
	}

	else {
		$pagination = User::Get_Page($page);
	}

	$pages = array();

	for ($i = 0; $i < $pagination["pages"]; $i++) {
		array_push($pages, array(
			"href" => "/admin/users?".http_build_query([
				"page" => $i + 1,
				"search" => $search,
			]),
			"text" => $i + 1,
		));
	}

    $page = new PageAdmin();

	$page -> setTpl("users", array(
		"users" => $pagination["data"],
		"search" => $search,
		"pages" => $pages,
	));
});

$app->get("/admin/users/create", function() {
	User::verifyLogin();

    $page = new PageAdmin();

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

	if (isset($_POST["des_password"]) == True) {
		$_POST["des_password"] = User::encrypt_decrypt("encrypt", User::KEY, $_POST["des_password"]);
		$_POST["des_password_show"] = User::encrypt_decrypt("decrypt", User::KEY, $_POST["des_password"]);
	}

    $page = new PageAdmin();

	$page -> setTpl("users-update", array(
		"user" => $user -> getValues(),
	));
});

$app->post("/admin/users/create", function() {
	User::verifyLogin();

	$user = new User();

	$_POST["is_admin"] = (isset($_POST["is_admin"])) ? 1 : 0;

	$testing_password_hash = False;

	if ($testing_password_hash == True) {
		$_POST["des_password"] = User::Get_Password_Hash($_POST["des_password"]);
	}

	$user -> setData($_POST);

	$user -> save();

	header("Location: /admin/users");
	exit;
});

$app->post("/admin/users/:id_user", function($id_user) {
	User::verifyLogin();

	$user = new User();

	$_POST["is_admin"] = (isset($_POST["is_admin"])) ? 1 : 0;

	$user -> get((int)$id_user);

	$user -> setData($_POST);

	$user -> update();

	header("Location: /admin/users");
	exit;
});

?>