<?php 
// tras as dependencias
require_once("vendor/autoload.php");
// são name space para trazer as classes
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;

$app = new Slim();

// para trazer os erros detalhados
$app->config('debug', true);
// carrega a pagina principal
$app->get('/', function() {
    // vai trazer o header
	$page = new Page();
	// irá trazer o index, e irá terminar a execução e irá retornar o footer
	$page->setTpl("index");

});
// carrega a página principal para administradores
$app->get('/admin', function() {
    // vai trazer o header
	$page = new PageAdmin();
	// irá trazer o index, e irá terminar a execução e irá retornar o footer
	$page->setTpl("index");

});
// faz rodar os comandos acima
$app->run();

 ?>