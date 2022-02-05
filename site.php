<?php 

use \Hcode\Page;
use \Hcode\Model\Product;

$app->get("/", function() {
	$products = Product::listAll();

    $page = new Page();

	$page -> setTpl("Index", array(
		"products" => Product::checkList($products),
	));
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

?>