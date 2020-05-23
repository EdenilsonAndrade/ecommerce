<?php 

use \Hcode\Model\User;

// função para formatar valores
function formatPrice(float $vlprice)
{

	return  number_format($vlprice, 2, ",", ".");
}

// função para checar o login
function checkLogin($inadmin = true)
{

	return User::checkLogin($inadmin);
}

// função para pegar o nome do login
function getUserName()
{

	$user = User::getFromSession();

	return $user->getdesperson();

}

 ?>