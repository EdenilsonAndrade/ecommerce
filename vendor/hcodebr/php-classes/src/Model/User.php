<?php 

namespace Hcode\Model;
// chama a classe sql
use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model {

	public static function login($login, $password) {

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));
		// verifica se retornou alguma login com o usuário informado 
		if (count($results) === 0) {

			trow new \Exception("Usuário inexistente ou senha inválida");

		}

		$data = $results[0];
		// verifica se o usuário e senha estão corretos
		if (password_verify($password, $data["despassword"]) === true) {

			$user = new User();

		} else {

			trow new \Exception("Usuário inexistente ou senha inválida");

		}

	}

}

 ?>