<?php
if (!isset($c_selfiche)) {
	$c_selfiche=true;
	class SelFiche {
		var $fiche;
		function SelFiche(&$fiche) {
			$this->fiche=&$fiche;
		}
		function getTree($niveau=0,$url="") {
			global $papa,$image_sql_url;
			global $action,$fichierordre,$idordre,$cherche;
			if ($niveau==0) {
				$this->echoForm();
			}
			if ($cherche=="" && $this->fiche->forceCherche==true && $niveau==0) return;
			if ($fichierordre==$this->fiche->fichier->nom) {
				$this->fiche->read($idordre);
				$ordre=$this->fiche->getChamp("ordre");
				if ($action=="descend") {				
					$result=$this->fiche->query("select * from ".$this->fiche->fichier->nom." WHERE ".$this->fiche->champRefOrdre->nom."=".$this->fiche->champRefOrdre->getSQLString()." AND ordre>".$ordre->getSQLString()." ORDER BY ordre ASC limit 0,1");
					if (count($result)!=0) {
						$row=$result[0];
						$this->fiche->query("UPDATE ".$this->fiche->fichier->nom." SET ordre=".$ordre->getSQLString()." WHERE ".$this->fiche->cle->nom."=".$row[$this->fiche->cle->nom]);
						$this->fiche->query("UPDATE ".$this->fiche->fichier->nom." SET ordre=".$row[ordre]." WHERE ".$this->fiche->cle->nom."=".$idordre);
					}
				}
				if ($action=="monte") {
					$result=$this->fiche->query("select * from ".$this->fiche->fichier->nom." WHERE ".$this->fiche->champRefOrdre->nom."=".$this->fiche->champRefOrdre->getSQLString()." AND ordre<".$ordre->getSQLString()." ORDER BY ordre DESC limit 0,1");
					if (count($result)!=0) {
						$row=$result[0];
						$this->fiche->query("UPDATE ".$this->fiche->fichier->nom." SET ordre=".$ordre->getSQLString()." WHERE ".$this->fiche->cle->nom."=".$row[$this->fiche->cle->nom]);
						$this->fiche->query("UPDATE ".$this->fiche->fichier->nom." SET ordre=".$row[ordre]." WHERE ".$this->fiche->cle->nom."=".$idordre);
					}
				}
			}
			if ($papa=="")	$papa=$this->fiche->getSelPage();
			$url.="&cherche=$cherche";
			$pepe=urlencode($papa."&".$url);
			echo($this->getNiveauHTML($niveau));
			echo("<a href='".$this->fiche->getFormPage(1).$url."&pepe=$pepe'>".$this->fiche->getNiveauColor()."<u><b>*** ".$this->fiche->fichier->nom." : Ajouter</b></u></a>");
			echo("<br>");
			if ($niveau==0 && $cherche!="") {
				$res=$this->fiche->query($this->getSQLCherche());
			}else {
				$res=$this->fiche->query($this->getSQL());
			}
			$champSel=$this->fiche->getChampSel();
			//echo("<ul type=>");
			foreach($res as $k=>$v) {
				$this->fiche->affecteRow($v);
				if ($k==count($res)-1) {
					echo($this->getNiveauHTML($niveau,1));
				} else {
					echo($this->getNiveauHTML($niveau));
				}
				
				$liens=$this->fiche->liens;
				// affiche les liens vers les fichiers à ouvrir	
				if (isset($liens)) {
					$b="";
					foreach($liens as $lien) {
						// test si on doit ouvrir
						$champ_ref=$lien->getChamp();
						$cle=$this->fiche->cle->valeur;
						$glob=$GLOBALS[$champ_ref->nom];
						if ($cle!=$glob) {
							$fic=$lien->fiche->getFichier();
							echo("<a href='$papa".$url."&".$this->fiche->cle->nom."=".$this->fiche->cle->valeur."&".$champ_ref->nom."=".$this->fiche->cle->getString()."#".$this->fiche->fichier->nom."'><img border=0 src='".$image_sql_url."plus.gif'  alt='".$fic->nom."'>&nbsp;");
							echo("</a>");
						} else {
							$b="<b>";
							echo("<a name=".$this->fiche->fichier->nom."></a>");
							echo("<img src='".$image_sql_url."moins.gif' >&nbsp;");
						}
					}
				}
				$n=$this->fiche->getSelString();
				echo($this->fiche->getURLModifTree($this->fiche->getNiveauColor().$b."$n</b>",$url,$papa)."<br>");
				// Affiche le fichier lié si ouvert
				if (isset($liens)) {
					foreach($liens as $lien) {
						// test si on doit ouvrir
						$champ_ref=$lien->getChamp();
						$cle=$this->fiche->cle->valeur;
						$glob=$GLOBALS[$champ_ref->nom];
						
						if ($cle==$glob) {
//							echo("<a name=".$this->fiche->fichier->nom.">#####".$this->fiche->fichier->nom."######</a>");
							$sf=new SelFiche($lien->getFiche());
							$sf->setWhere(" WHERE ".$champ_ref->nom."=".$GLOBALS[$champ_ref->nom]);
							echo $sf->getTree($niveau+1,$url."&".$this->fiche->cle->nom."=".$this->fiche->cle->valeur."&".$champ_ref->nom."=".$GLOBALS[$champ_ref->nom]);
						} else {
						}
					}
				}
			}
			//$pepe=urlencode($papa."&".$url);
			
			//echo($this->getNiveauHTML($niveau,1)."<a href='".$this->fiche->getFormPage(1).$url."&pepe=$pepe'>".$this->fiche->getNiveauColor()."<i><b>--- Ajouter ".$this->fiche->fichier->nom." ---</b></i></a><br>");
			//echo("</ul>");
		}
		function getNiveauHTML($niveau,$last=0) {
			global $premier,$image_sql_url;
			return str_repeat("&nbsp;&nbsp;&nbsp;",$niveau*3);
			if ($premier!=1) {
				$premier=1;
				return "<img src='".$image_sql_url."coinhaut.gif' border=0>";
			}
			for ($i=0;$i<$niveau;$i++) {
				$out.="<img border=0 src='".$image_sql_url."vertical.gif'>";
//				$out.=("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			}
			if ($last==0) $out.="<img border=0 src='".$image_sql_url."intersec.gif'>";
			else $out.="<img border=0  src='".$image_sql_url."coinbas.gif'>";
			return $out;
		}
		// renvoi la chaine sql de sélection
		function getSQL() {
			$champSel=$this->fiche->getChampSel();
			$sql="SELECt ";
			$sqlc=$this->fiche->cle->nom;
			foreach($champSel as $v) {
				if ($sqlc!="") $sqlc.=",";
				$sqlc.=$v->nom;
			}
			$fichier=$this->fiche->getFichier();
			$sql.=$sqlc." FROM ".$fichier->nom." ".$this->getWhere()." ".$this->getOrder();
			return $sql;
		}// renvoi la chaine sql de sélection
		function getSQLCherche() {
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
		function getWhere() {
			return $this->fiche->getWhere();
		}
		function setWhere($w) { $this->fiche->setWhere($w);}
		function echoForm() {
			global $cherche;
			echo("<form action=".$this->fiche->getSelPage().">");
			echo("Recherche ".$this->fiche->fichier->nom." : ");
			echo("<input type=hidden name='typepage' value='sel'>");
			echo("<input type=input name='cherche' value='$cherche'>");
			echo(" dans ");
			$cs=$this->fiche->getChampCherche();
			foreach($cs as $v) {
				echo(" ".$v->nom);
			}
			echo("</form>");
		}
	}
}
?>
