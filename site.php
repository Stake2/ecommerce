<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

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

?>