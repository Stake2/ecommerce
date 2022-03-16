<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\Order;
use \Hcode\Model\Order_Status;
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
		"error" => Cart::Get_Error("Error"),
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

	$address = new Address();

	$cart = Cart::Get_From_Session();

	#if (isset($_GET["zip_code"]) == True) {
	#	$_GET["zip_code"] = $cart -> getdes_zip_code();
	#}

	if (isset($_GET["zip_code"]) == True) {
		$address -> Load_From_CEP($_GET["zip_code"]);

		$cart -> setdes_zip_code($_GET["zip_code"]);

		$cart -> save();

		$cart -> Get_Calculate_Total();
	}

	if (!$address -> getdes_address()) $address -> setdes_address("");
	if (!$address -> getdes_complement()) $address -> setdes_complement("");
	if (!$address -> getdes_district()) $address -> setdes_district("");
	if (!$address -> getdes_country()) $address -> setdes_country("");
	if (!$address -> getdes_city()) $address -> setdes_city("");
	if (!$address -> getdes_state()) $address -> setdes_state("");
	if (!$address -> getdes_zip_code()) $address -> setdes_zip_code("");

	$page = new Page();

	$page -> setTpl("checkout", array(
		"cart" => $cart -> getValues(),
		"address" => $address -> getValues(),
		"products" => $cart -> Get_Products(),
		"error" => Address::Get_Error(),
	));
});

$app->post("/checkout", function() {
	User::verifyLogin(False);

	$field_names = array(
		"zip_code",
		"des_address",
		"des_district",
		"des_city",
		"des_state",
		"des_country",
	);

	$portuguese_field_names = array(
		"o CEP",
		"o endereço",
		"o bairro",
		"a cidade",
		"o estado",
		"o país",
	);

	$i = 0;
	foreach ($field_names as $field_name) {
		$portuguese_field_name = $portuguese_field_names[$i];

		if (!isset($_POST[$field_name]) or $_POST[$field_name] == "") {
			Address::Set_Error("Informe ".$portuguese_field_name.".");

			header("Location: /checkout");
			exit;
		}

		$i++;
	}

	$user = User::Get_From_Session();

	$address = new Address();

	$_POST["des_zip_code"] = $_POST["zip_code"];
	$_POST["id_person"] = $user -> getid_person();

	$address -> setData($_POST);

	$address -> save();

	$cart = Cart::Get_From_Session();

	$totals = $cart -> Get_Calculate_Total();

	$order = new Order();

	$order -> setData(array(
		"id_cart" => $cart -> getid_cart(),
		"id_user" => $user -> getid_user(),
		"id_status" => Order_Status::EM_ABERTO,
		"id_address" => $address -> getid_address(),
		"vl_total" => $cart -> getvl_total(),
	));

	$order -> save();

	header("Location: /order/".$order -> getid_order());
	exit;
});

$app->get("/login", function() {
	$page = new Page();

	$page -> setTpl("login", array(
		"error" => User::Get_Message("Error"),
		"error_register" => User::Get_Message("Register"),
		"register_values" => (isset($_SESSION["register_values"])) ? $_SESSION["register_values"] : 
		array(
			"name" => "",
			"email" => "",
			"phone" => "",
		),
	));
});

$app->post("/login", function() {
	try {
		User::login($_POST["login"], $_POST["password"]);
	}

	catch (Exception $e) {
		User::Set_Message($e -> getMessage(), "Error");
	}

	header("Location: /checkout");
	exit;
});


$app->get("/logout", function() {
	User::logout();

	header("Location: /login");
	exit;
});

$app->get("/register", function() {
	var_dump("Tal");
	$_SESSION["register_values"] = $_POST;

	$field_names = array(
		"name",
		"email",
		"password",
	);

	$portuguese_field_names = array(
		"o seu nome",
		"o seu email",
		"a sua senha",
	);

	$i = 0;
	foreach ($field_names as $field_name) {
		$portuguese_field_name = $portuguese_field_names[$i];

		if (!isset($_POST[$field_name]) or $_POST[$field_name] == "") {
			User::Set_Message("Preencha ".$portuguese_field_name.".", "Register");

			header("Location: /login");
			exit;
		}

		$i++;
	}

	if (User::Check_If_Login_Exists($_POST["email"]) == True) {
		User::Set_Message("Este endereço de email já está sendo usado por outro usuário.", "Register");

		header("Location: /login");
		exit;
	}

	$user = new User();

	$user -> setData(array(
		"is_admin" => 0,
		"des_login" => $_POST["email"],
		"des_person" => $_POST["name"],
		"des_email" => $_POST["email"],
		"des_password" => $_POST["password"],
		"nr_phone" => $_POST["phone"],
	));

	$user -> save();

	User::login($_POST["email"], $_POST["password"]);

	header("Location: /checkout");
	exit;
});

$app->get("/forgot", function(){
	$page = new Page();
	
	$page->setTpl("forgot", array(
		"error_register" => User::Get_Message("Register"),
	));
});

$app->post("/forgot", function(){
	if(isset($_POST["email"]) == False or $_POST["email"] == ""){
		User::Set_Message("Favor digitar um endereço de e-mail válido.", "Register");

		header("Location: /forgot");
		exit;
	}

	if(User::Check_If_Login_Exists($_POST["email"]) == False){
		User::Set_Message("Este endereço de e-mail não existe na nossa base de dados.", "Register");

		header("Location: /forgot");
		exit;
	}

	$user = User::getForgot($_POST["email"], $is_admin = False);

	header("Location: /forgot/sent");
	exit;
});

$app->get("/forgot", function() {
	$page = new Page();

	$page -> setTpl("forgot");
});

$app->post("/forgot", function() {
	$user = User::getForgot($_POST["email"], $is_admin = False);

	header("Location: /forgot/sent");
	exit;
});

$app->get("/forgot/sent", function() {
	$page = new Page();

	$page -> setTpl("forgot-sent");
});

$app->get("/forgot/reset", function() {
	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page -> setTpl("forgot-reset", array(
		"name" => $user["des_person"],
		"code" => $_GET["code"],
	));
});

$app->post("/forgot/reset", function() {
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["id_recovery"]);

	$user = new User();

	$user -> get((int)$forgot["id_user"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, array(
		"cost" => 12,
	));

	$user -> setPassword($password);

	$page = new Page();

	$page -> setTpl("forgot-reset-success");
});

$app->get("/profile", function() {
	User::verifyLogin(False);

	$user = User::Get_From_Session();

	$page = new Page();

	$page -> setTpl("profile", array(
		"user" => $user -> getValues(),
		"profile_message" => User::Get_Message("Success"),
		"profile_error" => User::Get_Message("Error"),
	));
});

$app->post("/profile", function() {
	User::verifyLogin(False);

	if (!isset($_POST["des_person"]) or $_POST["des_person"] === "") {
		User::Set_Message("Preencha seu nome.", "Error");

		header("Location: /profile");
		exit;
	}

	if (!isset($_POST["des_email"]) or $_POST["des_email"] === "") {
		User::Set_Message("Preencha seu e-mail.", "Error");

		header("Location: /profile");
		exit;
	}

	$user = User::Get_From_Session();

	if ($_POST["des_email"] !== $user -> getdes_email()) {
		if (User::Check_If_Login_Exists($_POST["email"]) == True) {
			User::Set_Message("Este endereço de email já está cadastrado.", "Error");

			header("Location: /profile");
			exit;
		}
	}

	$_POST["id_user"] = $user -> getid_user();
	$_POST["is_admin"] = $user -> getis_admin();
	$_POST["des_password"] = $user -> getdes_password();
	$_POST["des_login"] = $_POST["des_email"];

	$user -> setData($_POST);

	$user -> update();

	$_SESSION[User::SESSION] = $user -> getValues();

	User::Set_Message("Dados alterados com sucesso.", "Success");

	header("Location: /profile");
	exit;
});

$app->get("/order/:id_order", function($id_order) {
	User::verifyLogin(False);

	$order = new Order();

	$order -> get((int)$id_order);

	$page = new Page();

	$page -> setTpl("payment", array(
		"order" => $order -> getValues(),
	));
});

$app->get("/boleto/:id_order", function($id_order) {
	User::verifyLogin(False);

	$order = new Order();

	$order -> get((int)$id_order);

	require "res/boletophp/variaveis.php";

	// DADOS DO BOLETO PARA O SEU CLIENTE
	$valor_cobrado = formatPrice($order -> getvl_total()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ",", "");
	$dados_boleto["nosso_numero"] = $order -> getid_order();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dados_boleto["numero_documento"] = $order -> getid_order();	// Num do pedido ou nosso numero

	// DADOS DO SEU CLIENTE
	$dados_boleto["sacado"] = $order -> getdes_person();
	$dados_boleto["endereco1"] = $order -> getdes_address()." ".$order -> getdes_district();
	$dados_boleto["endereco2"] = $order -> getdes_city()." ".$order -> getdes_state()." ".$order -> getdes_country()." - CEP: ".$order -> getdes_zip_code();
	$dados_boleto["valor_boleto"] = $valor_boleto;

	require "res/boletophp/boleto_itau.php";
});

?>