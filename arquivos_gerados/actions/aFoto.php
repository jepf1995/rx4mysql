<?php

require_once 'model/entities/mFoto.php';

class aFoto extends mFoto {

	protected $sqlInsert="insert into foto (Foto, CodAvaliacao) values ('%s', '%s')";

	protected $sqlUpdate="update foto set Foto='%s', CodAvaliacao='%s' where CodFoto ='%s' ";

	protected $sqlSelect="select * from foto where %s %s";

	protected $sqlDelete="delete from foto where CodFoto ='%s' ";


	public function Insert(){
		try {
			$sql = sprintf($this->sqlInsert,$this->getFoto(),$this->getCodavaliacao());
			return $this->RunSelect($sql);
		} catch (Exception $e) {
			echo "Caught exception:",$e->getMessage(), "\n";
		}
	}

	public function Update(){
		try {
			$sql = sprintf($this->sqlUpdate, $this->getFoto(), $this->getCodavaliacao(),$this->getCodfoto());
			return $this->RunSelect($sql);
		} catch (Exception $e) {
			echo "Caught exception:",$e->getMessage(), "\n";
		}
	}

	public function Select($where="",$order="",$rquery=false){
		try {
			$sql = sprintf($this->sqlSelect, $where, $order);
			if ($rquery)
				return $sql;
			else
				return $this->RunSelect($sql);
		} catch (Exception $e) {
			echo "Caught exception:",$e->getMessage(), "\n";
		}
	}

	public function Delete(){
		try {
			$sql = sprintf($this->sqlDelete,$this->getCodfoto());
			return $this->RunQuery($sql);
		} catch (Exception $e) {
			echo "Caught exception:",$e->getMessage(), "\n";
		}
	}

	public function load() {
		try {
			$rs = $this->Select(sprintf("and CodFoto ='%s' ",$this->getCodfoto()));
			$this->setCodavaliacao($rs[0]["CodAvaliacao"]);

			return $this;

		} catch (Exception $e) {
			echo "Caught exception:",$e->getMessage(), "\n";
		}
	}

}

?>
