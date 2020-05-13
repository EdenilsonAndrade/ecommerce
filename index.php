<?php 

session_start();
// tras as dependencias
require_once("vendor/autoload.php");
// são name space para trazer as classes
use \Slim\Slim;

$app = new Slim();

// para trazer os erros detalhados
$app->config('debug', true);

require_once("functions.php");
require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");

// faz rodar os comandos acima
$app->run();

 ?>