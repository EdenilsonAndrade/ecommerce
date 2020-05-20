<?php 
// classe para carrinho
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model {

	const SESSION = "Cart";
	// metodo para verificar se o carrinho já existe
	public static function getFromSession()
	{

		$cart = new Cart();

		if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
		}
		else
		{

			$cart->getFromSessionID();

			if (!(int)$cart->getidcart() > 0) {

				$data = [
					"dessessionid"=>session_id()
				];

				if (User::checkLogin(false)) {

					$user = User::getFromSession();

					$data['iduser'] = $user->getiduser();

				}

				$cart->setData($data);

				$cart->save();

				$cart->setToSession();
				

			}
		}

		return $cart;
	}
	// metodo para colocar o carrinho na sessão
	public function setToSession()
	{

		$_SESSION[Cart::SESSION] = $this->getValues();

	}

	// metodo para verificar se o id da sessão contém no banco de dados
	public function getFromSessionID()
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			":dessessionid"=>session_id()
		]);

		if (count($results) > 0){

		$this->setData($results[0]);
		
		}
	}


	// metodo para pegar os dados do carrinho
	public function get(int $idcart)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			":idcart"=>$idcart
		]);

		if (count($results) > 0){

		$this->setData($results[0]);

		}
	}

	// metodo para salvar os dados do carrinho
	public function save()
	{

		$sql = new Sql();

		date_default_timezone_set("America/Sao_Paulo");

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			":idcart"=>$this->getidcart(),
			":dessessionid"=>$this->getdessessionid(),
			":iduser"=>$this->getiduser(),
			":deszipcode"=>$this->getdeszipcode(),
			":vlfreight"=>$this->getvlfreight(),
			":nrdays"=>$this->getnrdays()
		]);

		$this->setData($results[0]);
	}
	


}

 ?>