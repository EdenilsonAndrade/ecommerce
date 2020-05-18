<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

// carrega a pagina principal
$app->get('/', function() {

	$products = Product::listAll();
    // vai trazer o header
	$page = new Page();
	// irá trazer o index, e irá terminar a execução e irá retornar o footer
	$page->setTpl("index", [
		"products"=>Product::checkList($products)
	]);

});

// metodo para chamar a categoria selecionada no site na página principal
$app->get("/categories/:idcategory", function($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>Product::checkList($category->getProducts())
	]);

});

 ?>