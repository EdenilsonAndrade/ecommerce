<?php 

use \Hcode\Page;
use \Hcode\Model\Product;

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

 ?>