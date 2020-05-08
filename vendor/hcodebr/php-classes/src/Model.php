<?php 
// metodo para verificar se é get ou set e já inserir os valores
namespace Hcode;

class Model {

	private $values = [];

	public function __call($name, $args) { // como parametro irá receber o nome no primeiro parametro e no segundo irá receber o valor do mesmo
		
		$method = substr($name, 0, 3); // identifica se o metodo é set ou get
		$fieldName = substr($name, 3, strlen($name)); // pega o nome do campo

		switch ($method)
		{

			case "get":
				return $this->values[$fieldName]; //verifica a variavel pivate $values
			break;

			case "set":
				$this->values[$fieldName] = $args[0]; //seta o valor do args
			break;
		}

	}
	// aqui irá inserir os dados dos get e set dinamicamente
	public function setData($data = array())
	{

		foreach ($data as $key => $value) {
			
			$this->{"set".$key}($value);

		}

	}

	public function getValues()
	{

		return $this->values; //retorna os valores da variavel private $values

	}

}

 ?>