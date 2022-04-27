<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\Order_Status;

$app->get("/admin/orders/:id_order/status", function($id_order) {
	User::verifyLogin();

	$order = new Order();

	$order -> get((int)$id_order);

    $page = new PageAdmin();

	$page -> setTpl("order-status", array(
		"order" => $order -> getValues(),
		"status" => Order_Status::List_All(),
		"error_message" => Order::Get_Message("Error"),
		"success_message" => Order::Get_Message("Success"),
	));
});

$app->post("/admin/orders/:id_order/status", function($id_order) {
	User::verifyLogin();

	if (!isset($_POST["id_status"]) or !(int)$_POST["id_status"] > 0) {
		Order::Set_Message("Informe o status atual.", "Error");

		header("Location: /admin/orders/".$id_order."/status");
		exit;
	}

	$order = new Order();

	$order -> get((int)$id_order);

	$order -> setid_status((int)$_POST["id_status"]);

    $order -> save();

	Order::Set_Message("Status atualizado.", "Success");

	header("Location: /admin/orders/".$id_order."/status");
	exit;
});

$app->get("/admin/orders/:id_order/delete", function($id_order) {
	User::verifyLogin();

	$order = new Order();

	$order -> get((int)$id_order);

	$order -> delete();

    header("Location: /admin/orders");
	exit;
});

$app->get("/admin/orders/:id_order", function($id_order) {
	User::verifyLogin();

	$order = new Order();

	$order -> get((int)$id_order);

	$cart = $order -> Get_Cart();

    $page = new PageAdmin();

	$page -> setTpl("order", array(
		"order" => $order -> getValues(),
		"cart" => $cart -> getValues(),
		"products" => $cart -> Get_Products(),
	));
});

$app->get("/admin/orders", function() {
	User::verifyLogin();

    $page = new PageAdmin();

	$page -> setTpl("orders", array(
		"orders" => Order::List_All(),
	));
});

?>