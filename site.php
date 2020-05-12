<?php 

use \Hcode\Page;

// carrega a pagina principal
$app->get('/', function() {
    // vai trazer o header
	$page = new Page();
	// irá trazer o index, e irá terminar a execução e irá retornar o footer
	$page->setTpl("index");

});

 ?>