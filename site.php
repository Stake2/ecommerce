<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

$app->get("/", function() {
	$products = Product::listAll();

    $page = new Page();

	$page -> setTpl("Index", array(
		"products" => Product::checkList($products),
	));
});

$app->get("/categories/:id_category", function($id_category) {
	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	$category = new Category();

	$category -> get((int)$id_category);

	$pagination = $category -> getProductsPage($page);

	$pages = array();

	for ($i = 1; $i <= $pagination["pages"]; $i++) {
		array_push($pages, array(
			"link" => "/categories/".$category -> getid_category()."?page=".$i,
			"page" => $i,
		));
	}

	$page = new Page();

	$page -> setTpl("category", array(
		"category" => $category -> getValues(),
		"products" => $pagination["data"],
		"pages" => $pages,
	));
});

$app->get("/products/:des_url", function($des_url) {
	$product = new Product();

	$product -> get_From_URL($des_url);

	$page = new Page();

	$page -> setTpl("product-details", array(
		"product" => $product -> getValues(),
		"categories" => $product -> Get_Categories(),
	));
});

$app->get("/cart", function() {
	$page = new Page();

	$cart = Cart::Get_From_Session();

	$page -> setTpl("cart", array(
		"cart" => $cart -> getValues(),
		"products" => $cart -> Get_Products(),
		"error" => Cart::Get_Message_Error(),
	));
});

$app->get("/cart/:id_product/add", function($id_product) {
	$product = new Product();

	$product -> get((int)$id_product);

	$cart = Cart::Get_From_Session();

	$quantity = (isset($_GET["quantity"])) ? (int)$_GET["quantity"] : 1;

	for ($i = 0; $i < $quantity; $i++) {
		$cart -> Add_Product($product);
	}

	header("Location: /cart");
	exit;
});

$app->get("/cart/:id_product/minus", function($id_product) {
	$product = new Product();

	$product -> get((int)$id_product);

	$cart = Cart::Get_From_Session();

	$cart -> Remove_Product($product);

	header("Location: /cart");
	exit;
});

$app->get("/cart/:id_product/remove", function($id_product) {
	$product = new Product();

	$product -> get((int)$id_product);

	$cart = Cart::Get_From_Session();

	$cart -> Remove_Product($product, True);

	header("Location: /cart");
	exit;
});

$app->post("/cart/freight", function() {
	$cart = Cart::Get_From_Session();

	$cart -> Set_Freight($_POST["zip_code"]);

	header("Location: /cart");
	exit;
});

$app->get("/checkout", function() {
	User::verifyLogin(False);

	$cart = Cart::Get_From_Session();

	$address = new Address();

	$page = new Page();

	$page -> setTpl("checkout", array(
		"cart" => $cart -> getValues(),
		"address" => $address -> getValues(),
	));
});

$app->get("/login", function() {
	$page = new Page();

	$page -> setTpl("login", array(
		"error" => User::Get_Error(),
	));
});

$app->post("/login", function() {
	try {
		User::login($_POST["login"], $_POST["password"]);
	}

	catch (Exception $e) {
		User::Set_Error($e -> getMessage());
	}

	header("Location: /checkout");
	exit;
});


$app->get("/logout", function() {
	User::logout();

	header("Location: /login");
	exit;
});

?>