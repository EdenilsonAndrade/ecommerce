<?php 
// classe para produtos
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model {

	// metodo para listar todos os produtos
	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
	}
	// metodo para carregar as fotos dos produtos para as paginas  do site
	public static function checkList($list)
	{

		foreach ($list as &$row) {
			
			$p = new Product();
			$p->setData($row);
			$row = $p->getValues();

		}

		return $list;

	}

	// metodo para cadastrar/alterar produtos
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		));

		$this->setData($results[0]); // para retornar o usuário cadastrado

	}
	// metodo para verificar se o produto existe
	public function get($idproduct)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
			":idproduct"=>$idproduct
		]);

		$this->setData($results[0]);
	}
	// metodo para deletar produto
	public function delete()
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
			":idproduct"=>$this->getidproduct()
		]);

		$product = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
			"res" . DIRECTORY_SEPARATOR . 
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg";

		unlink($product); // deleta a foto do produto

	}
	// metodo para verificar se o produto contém foto
	public function checkPhoto()
	{

		if (file_exists(
			$_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
			"res" . DIRECTORY_SEPARATOR . 
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg"
		)) 
		{

			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg"; //aqui não está utilizando o DIRECTORY_SEPARATOR	pois é url e não diretório
		}
		else
		{
			$url = "/res/site/img/product.jpg";
		}

		$this->setdesphoto($url);

	}

	// metodo para salvar as fotos
	public function getValues()
	{

		$this->checkPhoto();

		$values = parent::getValues();

		return $values;

	}	
	// metodo para fazer upload de fotos
	public function setPhoto($file)
	{

		$extension = explode('.', $file['name']); //para encontrar o ponto
		$extension = end($extension); //para pegar apenas a extensão após o ponto

		switch ($extension) { //verifica qual a extensão para criar o arquivo temporário
			
			case 'jpg':
			case 'jpeg':
			$image =imagecreatefromjpeg($file["tmp_name"]);
			break;

			case 'gif':
			$image =imagecreatefromgif($file["tmp_name"]);
			break;

			case 'png':
			$image =imagecreatefrompng($file["tmp_name"]);
			break;

		}

		$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
			"res" . DIRECTORY_SEPARATOR . 
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg";

		imagejpeg($image, $dist);//converte a imagem para jpg

		imagedestroy($image);

		$this->checkPhoto();
	}
	// metodo para trazer o produto da desurl
	public function getFromURL($desurl)
	{

		$sql = new Sql();

		$rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl", [
			":desurl"=>$desurl
		]);

		$this->setData($rows[0]);
	}
	// metodo para retornar as categorias dos produtos
	public function getCategories()
	{

		$sql = new Sql();

		return $sql->select("
			SELECT * FROM tb_categories a 
			INNER JOIN tb_productscategories b USING (idcategory)
			WHERE b.idproduct = :idproduct
		", [
			":idproduct"=>$this->getidproduct()
		]);
	}

	public function verificaDesURL()
	{

		$sql = new Sql();

		 return $results = $sql->select("
		 	SELECT desproduct FROM tb_products
		 	WHERE desurl IN (
			SELECT desurl FROM tb_products 
			GROUP BY desurl
			HAVING COUNT(*)>1)");
	}

}

 ?>