<?php
if (!isset($c_formfiche)) {
	$c_formfiche=1;
	
	class FormFiche {
		var $fiche;
		function FormFiche(&$fiche) {
			$this->fiche=&$fiche;
		}
		function run() {
			global $action,$cle,$efface,$delete,$record,$read,$add;
			if (isset($cle)) {
				$this->fiche->read($cle);
			}
			if (isset($record)) {
				$this->record();
			}
			if (isset($add)) {
				$this->add();
			}
			if (isset($efface)) {
				$this->efface();
			}
			if (isset($delete)) {
				$this->delete();
			} 
			// test si on est en ajout, inutile de faire un affectenew
			// pour ne pas perdre les données déjà entrées
			if ($this->fiche->cle->getValeur()=="" && $add=="") {// ajout 
				$this->fiche->affecteGlobal();
				$this->fiche->affecteNew();
			}
			echo($this->fiche->getFormInput());			
		}
		function efface() {
			global $pepe;
			$this->fiche->affecteForm();
			echo("Souhaitez-vous réellement supprimer la fiche <b>".$this->fiche->cle->getString()." : ".$this->fiche->label->getString()."</b> ?<br>");
			echo("<a href=".$this->fiche->getFormPage()."&delete=1&".$this->fiche->cle->getURL()."&pepe=".urlencode($pepe).">Supprimer</a>&nbsp;");
			if (isset($pepe)) {
				echo("<a href='$pepe'>Annuler</a><br>");
			} else
				echo("<a href=".$this->fiche->getSelPage().">Annuler</a>");
			exit();
		}
		function delete() {
			$this->fiche->affecteGlobal();
			if ($this->fiche->delete()==1)
				echo("<h2>Fiche effacée</h2>");
			exit();
		}
		function add() {
			$this->fiche->affecteForm();
			$v=$this->fiche->isValide();
			if ($v=="") {	
				if ($this->fiche->add()>0)
				echo("<h2>Fiche ajoutée</h2>");
				exit();
			} else {
				echo ("<h2>Fiche Invalide</h2><font color=red>".$v."</font><hr>");
			}
		}
		function record() {
			$this->fiche->affecteForm();
			$v=$this->fiche->isValide();
			if ($v=="") {	
				if ($this->fiche->record()==1) {
					echo("<h2>Fiche enregistrée</h2>");
				}
				exit();
			} else {
				echo ("<h2>Fiche invalide</h2><font color=red>".$v."</font><hr>");
			}
		}
	}
}
?>
