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
	// metodo para trazer os produtos de determinada categoria
	public function getProducts($related = true)
	{

		$sql = new Sql();

		if ($related === true) 
		{

			return $sql->select("SELECT a.* FROM tb_products a 
						  INNER JOIN tb_productscategories b USING (idproduct)
						  WHERE b.idcategory = :idcategory", [
						  	":idcategory"=>$this->getidcategory()
						  ]);

		}
		else
		{

			return $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN (
						  SELECT a.idproduct FROM tb_products a 
						  INNER JOIN tb_productscategories b USING (idproduct)
						  WHERE b.idcategory = :idcategory)", [
						  	":idcategory"=>$this->getidcategory()
						  ]);
		}


	}

}

 ?>