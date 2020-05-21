<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;

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

// paginação da categoria selecionada no site na página principal
$app->get("/categories/:idcategory", function($idcategory){

	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i=1; $i <= $pagination["pages"]; $i++)
	{
		array_push($pages, [
     		"link"=>"/categories/".$category->getidcategory()."?page=".$i,
     		"page"=>$i
		]);
	}

	$page = new Page();

	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>$pagination["data"],
		"pages"=>$pages
	]);

});
// chama a pagina de detalhes do produto
$app->get("/products/:desurl", function($desurl){

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		"product"=>$product->getValues(),
		"categories"=>$product->getCategories()
	]);

});
// chama a tela do carrinho de compras
$app->get("/cart", function(){

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart", [
		"cart"=>$cart->getValues(),
		"product"=>$cart->getProducts()
	]);

});

// para adicionar produto no carrinho
$app->get("/cart/:idproduct/add", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession(); //recupera o carrinho ou cria

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i = 0; $i < $qtd; $i++) {

		$cart->addProduct($product);

	}

	header("Location: /cart");
	exit;

});

// para remover um produto do carrinho
$app->get("/cart/:idproduct/minus", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession(); //recupera o carrinho ou cria

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;

});

// para remover todos os itens do mesmo produto do carrinho
$app->get("/cart/:idproduct/remove", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession(); //recupera o carrinho ou cria

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;

});

 ?>