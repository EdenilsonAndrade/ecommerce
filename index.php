<?php 

require_once("vendor/autoload.php");

$app = new \Slim\Slim();

// para trazer os erros detalhados
$app->config('debug', true);

$app->get('/', function() {
    
	echo "OK";

});

$app->run();

 ?>