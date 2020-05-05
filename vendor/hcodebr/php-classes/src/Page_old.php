<?php 

namespace Hcode;

use Rain\Tpl;

class Page {

	private $tpl; //cria a variavel privada, para não ter acesso nas outras classes
	private $options = [];
	private $defaults = [
		"header"=>true,
		"footer"=>true,
		"data"=>[]
	];

	public function __construct($opts = array()){
			// a função array_merge mescla as informações, se der conflito com o primeiro parametro defaults, vai valer o segundo parametro $opts
			$this->options = array_merge($this->defaults, $opts);

			$config = array(
				"base_url"      => null,
				"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/", //caminho das paginas
				"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/", //caminho para o cache
				"debug"         => false // set to false to improve the speed
		    );

			Tpl::configure( $config );

			$this->tpl = new Tpl; //instacia a classe

			$this->setData($this->options["data"]);

			// irá imprimir na tela o header
			$this->tpl->draw("header");

	}
	// cria o metodo setData pois é utilizado no setTpl e no metodo construtor
	private function setData($data = array()) {

		foreach ($data as $key => $value) {
				$this->tpl->assign($key, $value);
			}
	}

	public function setTpl($name, $data = array(), $returnHTML = false) {

		$this->setData($data); 

		return $this->tlp-draw($name, $returnHTML);

	}

	public function __destruct() {
		// irá imprimir o footer
		$this->tpl->draw("footer");

	}

}

 ?>