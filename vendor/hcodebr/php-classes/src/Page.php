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

	public function __construct($opts = array())
	{
		// a função array_merge mescla as informações, se der conflito com o primeiro parametro defaults, vai valer o segundo parametro $opts
		$this->options = array_merge($this->defaults, $opts);

		$config = array(
		    "base_url"      => null,
		    "tpl_dir"       => $_SERVER['DOCUMENT_ROOT']."/views/", //caminho das paginas
		    "cache_dir"     => $_SERVER['DOCUMENT_ROOT']."/views-cache/", //caminho para o cache
		    "debug"         => false
		);

		Tpl::configure( $config );

		$this->tpl = new Tpl(); //instacia a classe

		if ($this->options['data']) $this->setData($this->options['data']); //seta os valores do metodo setData

		if ($this->options['header'] === true) $this->tpl->draw("header", false); //inicia o header

	}

	public function __destruct()
	{

		if ($this->options['footer'] === true) $this->tpl->draw("footer", false); //detroi o metodo buscando o footer

	}
	// cria o metodo setData pois é utilizado no setTpl e no metodo construtor
	private function setData($data = array())
	{

		foreach($data as $key => $val)
		{

			$this->tpl->assign($key, $val);

		}

	}

	public function setTpl($tplname, $data = array(), $returnHTML = false)
	{

		$this->setData($data);

		return $this->tpl->draw($tplname, $returnHTML);

	}

}

 ?>