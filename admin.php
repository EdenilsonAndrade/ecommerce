<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;

// carrega a página principal para administradores
$app->get('/admin', function() {

	User::verifyLogin(); //para verificar se o usuário está logado
    // vai trazer o header
	$page = new PageAdmin();
	// irá trazer o index, e irá terminar a execução e irá retornar o footer
	$page->setTpl("index");

});

// chama a tela de login 
$app->get('/admin/login', function() {
	// como o login.html já contém o footer e o header temos que desabilitar o mesmo da classe Page, que é a classe pai da PageAdmin
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");

});
// recebe as informações da tela de login
$app->post('/admin/login', function() {

	User::login($_POST["login"], $_POST["password"]);
	// se for informada o usuário e senha corretos irá passar para o index do admin
	header("Location: /admin");
	exit;

});
// para fazer logout
$app->get('/admin/logout', function() {

	User::logout();

	header("Location: /admin/login");
	exit;

});


 ?>