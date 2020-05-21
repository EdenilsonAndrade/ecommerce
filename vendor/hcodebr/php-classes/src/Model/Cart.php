<?php 
// classe para carrinho
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model {

	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";
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
	
	// metodo para adicionar produtos no carrinho de compras
	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", [
			":idcart"=>$this->getidcart(),
			":idproduct"=>$product->getidproduct()
		]);
	}

	// metodo para remover produtos do carrinho de compras
	public function removeProduct(Product $product, $all = false)
	{

		$sql = new Sql();

		if ($all) { //remove o produto do carrinho

			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
				":idcart"=>$this->getidcart(),
				":idproduct"=>$product->getidproduct()
			]);

		} else { // remove apenas um registro do produto

			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
				":idcart"=>$this->getidcart(),
				":idproduct"=>$product->getidproduct()
			]);

		}

	}

	// metodo para buscar todos os produtos do carrinho de compras
	public function getProducts()
	{

		$sql = new Sql();

		$rows = $sql->select("
			SELECT 
			b.idproduct, 
			b.desproduct, 
			b.vlprice, 
			b.vlwidth, 
			b.vlheight, 
			b.vllength, 
			b.vlweight,
			b.desurl,
			COUNT(*) AS nrqtd,
			SUM(b.vlprice) AS vltotal
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b USING (idproduct)
			WHERE a.idcart = :idcart
			AND a.dtremoved IS NULL
			GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
			ORDER BY b.desproduct
		", [
			":idcart"=>$this->getidcart()
		]);

		return Product::checkList($rows);
	}

	// metodo para trazer a soma dos itens do carrinho de compras
	public function getProductsTotals()
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT
			SUM(vlprice) AS vlprice,
			SUM(vlwidth) AS vlwidth,
			SUM(vlheight) AS vlheight,
			SUM(vllength) AS vllength,
			SUM(vlweight) AS vlweight,
			COUNT(*) AS nrqtd
			FROM
			tb_products a
			INNER JOIN tb_cartsproducts b USING (idproduct)
			WHERE b.idcart = :idcart
			AND b.dtremoved IS NULL
		", [
			":idcart"=>$this->getidcart()
		]);

		if (count($results) > 0) {
			return $results[0];
		} else {
			return [];
		}
	}

	// metodo para calcular o frete
	public function setFreight($nrzipcode)
	{

		$nrzipcode = str_replace('-', '', $nrzipcode);

		$totals = $this->getProductsTotals();

		if ($totals['nrqtd'] > 0) {

			if($totals['vlheight'] < 2) $totals['vlheight'] = 2;
			if($totals['vllength'] < 16) $totals['vllength'] = 16;

			$qs = http_build_query([
				'nCdEmpresa'=>'',
				'sDsSenha'=>'',
				'nCdServico'=>'41106', //aqui seria o tipo de frete, no caso foi deixado fixo porém poderia utilizar a opção para o cliente optar e utilizar uma opção no site para fazer a escolha
				'sCepOrigem'=>'09853120',
				'sCepDestino'=>$nrzipcode,
				'nVlPeso'=>$totals['vlweight'],
				'nCdFormato'=>'1', // formato, seria necessário verificar como o cliente irá encamihar e se houver mais de um teria que pegar a informação do banco de dados
				'nVlComprimento'=>$totals['vllength'],
				'nVlAltura'=>$totals['vlheight'],
				'nVlLargura'=>$totals['vlwidth'],
				'nVlDiametro'=>'0',
				'sCdMaoPropria'=>'S',
				'nVlValorDeclarado'=>$totals['vlprice'],
				'sCdAvisoRecebimento'=>'S'
			]);		

			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

			$result = $xml->Servicos->nCdServico;

			if ($result->MsgErro != '') {

				Cart::setMsgError($result->MsgErro);

			} else {

				Cart::clearMsgError();

			}

			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueTodecimal($result->Valor));
			$this->setdeszipcode($nrzipcode);

			$this->save();

			return $result;

		} else {


		}

	}

	// metodo para alterar a virgula por .
	public static function formatValueTodecimal($value):float
	{

		$value = str_replace('.', '', $value);
		return str_replace(',', '.', $value);
	}
	// para pegar a mensagem de erro
	public static function setMsgError($msg)
	{

		$_SESSION[Cart::SESSION_ERROR] = $msg;
	}
	// metodo para setar o erro na constante
	public static function getMsgError()
	{

		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

		Cart::clearMsgError();

		return $msg;

	}
	// metodo para limpar
	public static function clearMsgError()
	{

		$_SESSION[Cart::SESSION_ERROR] = NULL;
	}

}

 ?>