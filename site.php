<?php 

use \Hcode\Page;

$app->get("/", function() {
    $page = new Page();

	$page -> setTpl("Index");
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