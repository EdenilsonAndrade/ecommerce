<?php 
// classe para categorias
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model {

	// metodo para listar todas as categorias dos produtos
	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
	}
	// metodo para cadastrar categoria
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));

		$this->setData($results[0]); // para retornar o usuário cadastrado

		Category::updateFile();

	}
	// metodo para verificar se a categoria existe
	public function get($idcategory)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
			":idcategory"=>$idcategory
		]);

		$this->setData($results[0]);
	}
	// metodo para deletar categoria
	public function delete()
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
			":idcategory"=>$this->getidcategory()
		]);

		Category::updateFile();
	}
	// metodo para atualizar a lista de categorias para os cliente ao acessar o site
	public static function updateFile()
	{

		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));// o implode altera de array para string

	}
	// metodo para trazer os produtos para relacionar com as categorias
	public function getProducts($related = true)
	{

		$sql = new Sql();

		if ($related === true) 
		{

			return $sql->select("SELECT a.* FROM tb_products a 
						  INNER JOIN tb_productscategories b USING (idproduct)
						  WHERE b.idcategory = :idcategory
						  ORDER BY a.desproduct", [
						  	":idcategory"=>$this->getidcategory()
						  ]);

		}
		else
		{

			return $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN (
						  SELECT a.idproduct FROM tb_products a 
						  INNER JOIN tb_productscategories b USING (idproduct)
						  WHERE b.idcategory = :idcategory)
						  ORDER BY desproduct", [
						  	":idcategory"=>$this->getidcategory()
						  ]);
		}


	}
	// metodo para adicionar produto na categoria
	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT COUNT(*) FROM tb_productscategories WHERE idproduct = :idproduct", [
			":idproduct"=>$product->getidproduct()

			
		]);
		// faz a validação para verificar se o produto já está em outra categoria e exclui o mesmo para não conter o mesmo produto em duas categorias
		if ($results[0] > 0)
		{

			$sql->query("DELETE FROM tb_productscategories WHERE idproduct = :idproduct", [
			":idproduct"=>$product->getidproduct()
		]);
			$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)", [
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		]);

		}
		else
		{

			$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)", [
				":idcategory"=>$this->getidcategory(),
				":idproduct"=>$product->getidproduct()
			]);
		}

	}

	// metodo para remover produto na categoria
	public function removeProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		]);

	}

}

 ?>