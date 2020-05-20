<?php 
// classe para ajustes de usu�rios
namespace Hcode\Model;
// chama a classe sql
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {

	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret"; //tem que conter no minimo 16 caracteres
	const SECRET_IV = "HcodePhp7_Secret_IV"; //tem que conter no minimo 16 caracteres
	
	// metodo para pegar os dados da sess�o
	public static function getFromSession()
	{

		$user = new User();

		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {

			$user->SetData($_SESSION[User::SESSION]);
			
		}

		return $user;

	}

	// metodo para verificar se o usu�rio est� logado
	public static function checkLogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION]) //verifica se cont�m a sess�o 
			||
			!$_SESSION[User::SESSION] //verifica se est� vazia
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		) {
			// N�o est� logado
			return false;
		}
		else
		{

			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {

				return true;

			} else if ($inadmin === false) {

				return true;

			} else {

				return false;
			}

		}

	}

	// metodo para validar usu�rio e senha
	public static function login($login, $password) {

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));
		// verifica se retornou alguma login com o usu�rio informado 
		if (count($results) === 0) {

			throw new \Exception("Usu�rio inexistente ou senha inv�lida.");

		}

		$data = $results[0];
		// verifica se o usu�rio e senha est�o corretos
		if (password_verify($password, $data["despassword"]) === true) {

			$user = new User();

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues(); //cria a sess�o com os dados do usu�rio, busca da classe Model

			return $user;

		} else {

			throw new \Exception("Usu�rio inexistente ou senha inv�lida.");

		}

	}
	// metodo para veifiricar se o usu�rio est� logado
	public static function verifylogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION]) //verifica se cont�m a sess�o 
			||
			!$_SESSION[User::SESSION] //verifica se est� vazia
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 //verificar o id do usu�rio
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin //verifica se o usu�rio � admin
		) {

			header("Location: /admin/login"); //se n�o estiver definida direciona para a tela de login
			exit;

		}

	}
	// para fazer logout
	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}
	// metodo para listar todos os usu�rios
	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users user 
					INNER JOIN tb_persons per USING(idperson)
					ORDER BY per.desperson");
	}
	
	// metodo para inserir usu�rio
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]); // para retornar o usu�rio cadastrado
	}

	// metodo para retornar o usu�rio que foi selecionado para ser alterado
	public function get($iduser)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
				":iduser"=>$iduser
			));

			$this->setData($results[0]); //retorna o usu�rio informado
	}

	// metodo para salvar as altera��es no banco de dados

	public function update()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]); // para retornar o usu�rio alterado	
	}
	// metodo para deletar usu�rio
	public function delete()
	{

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
	}
	// metodo para recuperar a senha

	public static function getForgot($email, $inadmin = true)
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT * FROM tb_persons a
			INNER JOIN tb_users b USING (idperson)
			WHERE a.desemail = :email
		", array(
			":email"=>$email
		));

		if (count($results) === 0)
		{

			throw new \Exception("N�o foi poss�vel recuperar a senha.");

		} 
		else 
		{

			$data = $results[0];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]
			));

			if (count($results2) === 0)
			{

				throw new \Exception("N�o foi poss�vel recuperar a senha.");

			} 
			else 
			{

				$dataRecovery = $results2[0];

				$code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

				$code = base64_encode($code);
				
					if ($inadmin === true) 
					{

						$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

					} 
					else 
					{

						$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
						
					}				
					// parametros da classe construtor do Mailer, email, nome do usu�rio, assunto, layout html, nome que ir� no html, link que ir� no html do e-mail
					$mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha do usu�rio " . $data['desperson'], "forgot", array(
						"name"=>$data['desperson'],
						"link"=>$link
					));	

					$mailer-> CharSet = 'ISO-8859-1';			

					$mailer->send();

					return $link;

				}

			}

		}
		// metodo para validar a senha do usu�rio
		public static function validForgotDecrypt($code)
		{

			$code = base64_decode($code);

			$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

			$sql = new Sql();

			$results = $sql->select("
				SELECT *
				FROM tb_userspasswordsrecoveries a
				INNER JOIN tb_users b USING(iduser)
				INNER JOIN tb_persons c USING(idperson)
				WHERE
					a.idrecovery = :idrecovery
					AND
					a.dtrecovery IS NULL
					AND
					DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
			", array(
				":idrecovery"=>$idrecovery
			));

			if (count($results) === 0)
			{
				throw new \Exception("N�o foi poss�vel recuperar a senha.");
			}
			else
			{

				return $results[0];

			}

		}
		// metodo para informar que foi utilizado o c�digo de recupera��o
		public static function setForgotUsed($idrecovery)
		{

			$sql = new Sql();

			$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
				":idrecovery"=>$idrecovery
			));

		}
		// metodo para gravar a recupera��o da senha com a nova senha no banco
		public function setPassword($password)
		{

			$sql = new Sql();

			$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
				":password"=>$password,
				":iduser"=>$this->getiduser()
			));

		}
		// metodo para criptografar a senha
		public static function getPasswordHash($password)
		{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);

		}

}

 ?>