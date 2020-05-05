<?php 

require_once("vendor/autoload.php");

$app = new \Slim\Slim();

// para trazer os erros detalhados
$app->config('debug', true);

$app->get('/', function() {
    
	$sql = new Hcode\DB\Sql();

	$results = $sql->select("SELECT * FROM tb_users");

	echo json_encode($results);

});

$app->run();

 ?>