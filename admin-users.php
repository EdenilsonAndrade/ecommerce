<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;

// tela para listar todos os usuários
$app->get("/admin/users", function() {

	User::verifyLogin();

	$users = User::listAll(); //metodo para listar todos os usuários

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$users
	));

});
// acessa a tela para criar usuário
$app->get("/admin/users/create", function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

});

// para deletar um usuário no banco de dados
$app->get("/admin/users/:iduser/delete", function($iduser) {

	User::verifyLogin();	

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;

});

// acessa a tela para alterar usuário
$app->get("/admin/users/:iduser", function($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});

// salva o usuário que foi criado
$app->post("/admin/users/create", function() {

	User::verifyLogin();	

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0; //para informar se o usuário é admin ou não

	$_POST['despassword'] = User::getPasswordHash($_POST['despassword']);
	
	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;

});
// salva as alterações que foram feitas do usuário
$app->post("/admin/users/:iduser", function($iduser) {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0; //para informar se o usuário é admin ou não

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;

});

// chama a tela esqueceu a senha
$app->get("/admin/forgot", function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");	

});
// encaminha o e-mail para a solicitação de recuperação de senha
$app->post("/admin/forgot", function() {

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;

});
// tras a tela de e-mail enviado com sucesso para a solicitação de recuperação de senha
$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");	

});
// link para recuperar a senha
$app->get("/admin/forgot/reset", function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array (
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});
// encaminha nova senha
$app->post("/admin/forgot/reset", function() {

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);	

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = User::getPasswordHash($_POST['password']);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");	

});


 ?>