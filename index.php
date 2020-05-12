<?php 

session_start();
// tras as dependencias
require_once("vendor/autoload.php");
// são name space para trazer as classes
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

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

// chama a tela de categorias de produtos
$app->get("/admin/categories", function() {

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories", [
		'categories'=>$categories
	]);	

});
// chama a tela de cadastro de categoria 
$app->get("/admin/categories/create", function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");	

});
// grava as informações da categoria cadastrada
$app->post("/admin/categories/create", function() {

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;	

});
// metodo para excluir categoria
$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory); //verifica se o metodo ainda existe

	$category->delete();

	header("Location: /admin/categories");
	exit;

});

// chama a tela de alteração de categoria
$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);
	
	$page = new PageAdmin();

	$page->setTpl("categories-update", [
		"category"=>$category->getValues()
	]);	

});
// salva a alteração da categoria
$app->post("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();
	
	$category = new Category();

	$category->get((int)$idcategory);
	
	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;

});


// faz rodar os comandos acima
$app->run();

 ?>