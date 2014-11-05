<?php

require_once 'model/db/dbConnection.php';

class mFoto extends dbConnection {
	private $CodFoto;
	private $Foto;
	private $CodAvaliacao;

	public function getCodfoto(){
		return $this->CodFoto;
	}

	public function getFoto(){
		return $this->Foto;
	}

	public function getCodavaliacao(){
		return $this->CodAvaliacao;
	}


	public function setCodfoto($Codfoto){
		$this->CodFoto=$Codfoto;
	}

	public function setFoto($Foto){
		$this->Foto=$Foto;
	}

	public function setCodavaliacao($Codavaliacao){
		$this->CodAvaliacao=$Codavaliacao;
	}

}
?>
