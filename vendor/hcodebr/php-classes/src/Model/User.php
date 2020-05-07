<?php 
// classe para validar o login e senha
namespace Hcode\Model;
// chama a classe sql
use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model {

	const SESSION = "User";

	public static function login($login, $password) {

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));
		// verifica se retornou alguma login com o usuário informado 
		if (count($results) === 0) {

			throw new \Exception("Usuário inexistente ou senha inválida.");

		}

		$data = $results[0];
		// verifica se o usuário e senha estão corretos
		if (password_verify($password, $data["despassword"]) === true) {

			$user = new User();

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues(); //cria a sessão com os dados do usuário, busca da classe Model

			return $user;

		} else {

			throw new \Exception("Usuário inexistente ou senha inválida.");

		}

	}
	// metodo para veifiricar se o usuário está logado
	public static function verifylogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION]) //verifica se contém a sessão 
			||
			!$_SESSION[User::SESSION] //verifica se está vazia
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 //verificar o id do usuário
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin //verifica se o usuário é admin
		) {

			header("Location: /admin/login"); //se não estiver definida direciona para a tela de login
		exit;

		}

	}
	// para fazer logout
	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}

}

 ?>