<?php
if (!isset($c_cherchefiche)) {
	$c_cherchefiche=true;
	class ChercheFiche {
		var $fiche;
		var $moimeme;
		function ChercheFiche(&$fiche) {
			$this->fiche=&$fiche;
			$this->moimeme=$moimeme;
		}
		function run($niveau=0,$url="") {
			global $papa,$inc_sql_url,$cherche;
			
			if ($papa=="")	$papa=$this->fiche->getCherchePage()."&cherche=$cherche";
			
			$this->echoForm();
			$sql=$this->getSQL();
			if ($sql!="") {
				$res=$this->fiche->query($sql);
				foreach($res as $v) {
					$this->fiche->affecteRow($v);
					echo($this->fiche->getUrlModif($papa)."<br>");
				}
			}
		}
		function echoForm() {
			global $cherche;
			echo("<form action=".$this->fiche->getSelPage().">");
			echo("Recherche ".$this->fiche->fichier->nom." : ");
			echo("<input type=hidden name='typepage' value='cherche'>");
			echo("<input type=input name='cherche' value='$cherche'>");
			echo(" dans ");
			$cs=$this->fiche->getChampCherche();
			foreach($cs as $v) {
				echo(" ".$v->nom);
			}
			echo("</form>");
		}
		// renvoi la chaine sql de sélection
		function getSQL() {
			global $cherche;
			if ($cherche=="") return "";
			$champSel=$this->fiche->getChampSel();
			$sql="SELECT ";
			$sqlc=$this->fiche->cle->nom;
			$sqlw=" WHERE ";
			foreach($champSel as $v) {
				$sqlc.=",";
				$sqlc.=$v->nom;
			}
			$champCherche=$this->fiche->getChampCherche();
			$sqlw=" WHERE ";
			foreach($champCherche as $v) {
				if ($sqlw!=" WHERE ") $sqlw.=" OR ";
				$sqlw.=$v->nom." LIKE '%".$cherche."%' ";
			}
			$fichier=$this->fiche->getFichier();
			$sql.=$sqlc." FROM ".$fichier->nom." ".$sqlw.$this->getOrder();
			//echo($sql."<br>");
			return $sql;
		}
		
		function getOrder() {
			return $this->fiche->getOrder();
		}
	}
}
?>
