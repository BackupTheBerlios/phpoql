<?php
if (!isset($c_formtout)) {
	$c_formtout=1;
	
	class FormTout {
		var $fiche;
		function FormTout(&$fiche) {
			$this->fiche=&$fiche;
		}
		//function header() {
			//session_register("pepe");
			
		//}
		function run() {			
			global $typepage;
			global $pepe;
			if (isset($pepe)) {
				echo("<a href='$pepe'>Retour</a><br>");
			}
			if ($typepage=="") $typepage="sel";
			if ($typepage=="sel") {
				$sel=new SelFiche($this->fiche);
				$sel->getTree();
			} else if ($typepage=="form") {
				$form=new FormFiche($this->fiche);
				$form->run();
			}else if ($typepage=="cherche") {
				$form=new ChercheFiche($this->fiche);
				$form->run();
			}
		}
	}
}
?>
