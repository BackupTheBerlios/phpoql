<?php
if (!isset($c_fiche)) {
	$c_fiche=true;
	class Lien {
		var $fiche;
		var $champ;
		function Lien(&$fiche,&$champ) {
			$this->fiche=&$fiche;
			$this->champ=&$champ;
		}
		function getFiche() { return $this->fiche;}
		function getChamp() { return $this->champ;}
	}
	class Fiche {
		var $fichier;
		var $champs;
		var $cle;
		var $label;		
		var $champSel;
		var $champCherche;
		var $formPage;
		var $selPage;
		var $forceCherche;
		var $cherchePage;
		var $liens;
		var $where;
		var $champRefOrdre;
		var $color;
		// tableau des champs
		//function Fiche(&$champs) {
			//$this->champs=&$champs;
		//}
		function setFichier(&$fic) {
			$this->fichier=&$fic;
		}
		function getFichier() { return $this->fichier;}
		function setPage($p) { $this->formPage=$p; $this->selPage=$p; $this->cherchePage=$p;}
		function getFormPage($ajout=0) { 
			if ($ajout==0) return $this->formPage."?typepage=form";
			else return $this->formPage."?typepage=form&mode=ajout";
		}
		function getSelPage() { return $this->selPage."?typepage=sel";}
		function getCherchePage() { return $this->cherchePage."?typepage=cherche";}
		function setChampRefOrdre(&$c) { $this->champRefOrdre=&$c;}
		function addLien($l) { $this->liens[]=$l;}
		function &getChamp($c) { return $this->champs[$c];}
		function getNiveauColor() { if ($this->color=="") $this->color="<font color=black>"; return $this->color;}
		function setNiveauColor($c) { $this->color=$c;}
		function addChamp(&$champ) {
			$this->champs[$champ->nom]=&$champ;
			if (!isset($this->cle)) {
				$this->cle=&$champ;
				$champ->setLock(true);
			} else if (!isset($this->label)) {
				$this->label=&$champ;
			}
			$champ->setFiche($this);
		}
		function getSelString() {
			foreach($this->champSel as $k =>$v) {
				$n.=$this->champSel[$k]->getStringHuman()."&nbsp;";
			}
			return $n;
		}
		function getUrlModif($papa) {
			global $inc_sql_url;
			$pepe=urlencode($papa);
			foreach($this->champSel as $v) {
				$ch.="&nbsp;".$v->getString();
			}
			$out.= "<a href='".$this->getFormPage()."&cle=".$this->cle->valeur."&pepe=$pepe'>$ch</a>";
			return $out;
		}
		function getURLModifTree($ch="Modif",$cles="",$papa) {
			global $image_sql_url;
			$pepe=urlencode($papa."&".$cles);
			if (isset($this->champRefOrdre)) {
				
				$u="<a href='".$papa."&".$cles."&fichierordre=".$this->fichier->nom."&cleordre=".$this->cle->nom."&idordre=".$this->cle->valeur."&champrefordre=".$this->champRefOrdre."&refordre=".$this->champRefOrdre->valeur;
				$out.=$u."&action=monte'><img src='".$image_sql_url."up.gif' border=0 alt='monte'></a>";
				$out.="&nbsp;";
				$out.=$u."&action=descend'><img src='".$image_sql_url."down.gif' border=0 alt='descend'></a>";
				$out.="&nbsp;";
			}
			$out.= "<a href='".$this->getFormPage()."&typepage=form&cle=".$this->cle->valeur.$cles."&pepe=$pepe'>$ch</a>";
			return $out;
		}
		function getFormInput() {
			global $mode;
			$out.="<form action='".$this->formPage."' enctype='multipart/form-data' method='post'>";
			$out.="<table bgcolor=#cccccc width=100%><tr><td>";
			
			if ($mode!="ajout"){
				$out.="&nbsp;<input type='submit' name='record' value='Enregistrer'>";
				$out.="&nbsp;<input type='submit' name='efface' value='Supprimer'>";
			} else {
				$out.="&nbsp;<input type='submit' name='add' value='Ajouter'>";
			}
			$out.="<input type='hidden' name='mode' value=$mode>"; // ne pas perdre le mode AJOUT !
			$out.="</td></tr></table>";
			global $pepe;
			$out.="<input type='hidden' name=pepe value=$pepe>";
			$out.="<input type='hidden' name=typepage value=form>";
			$out.="<table border=0>";
			foreach($this->champs as $k=>$v) {
				$out.="<tr>";
				$out.= $v->getFormInput();
				$out.="</tr>";
			}
			$out.="</table>";
			$out.="<table bgcolor='#cccccc' width='100%'><tr><td>";
			if ($mode!="ajout") {
				$out.="&nbsp;<input type='submit' name='record' value='Enregistrer'>";
				$out.="&nbsp;<input type='submit' name='efface' value='Supprimer'>";
			} else {
				$out.="&nbsp;<input type='submit' name='add' value='Ajouter'>";
			}
			$out.="</td></tr></table>";
			$out.="</form>";
			return $out;
		}
		function isValide() {
			foreach($this->champs  as $k=>$v) {
				$valid.=$this->champs[$k]->isValide();
			}			
			return $valid;
		}
		function record() {
			$sql.="UPDATE ".$this->fichier->nom. " SET ";
			foreach($this->champs as $k=>$v) {
				if (!$this->champs[$k]->isFile()) {
					if ($sqlc!="") $sqlc.=", ";
					$sqlc.=$v->nom."=".$v->getSQLString();
				}
			}
			$sql.=$sqlc;
			$sql.=" WHERE ".$this->cle->getEgalSQL();	
			foreach($this->champs as $k=>$v) {
				if ($this->champs[$k]->isFile()) {
					$this->champs[$k]->setValeur($this->cle->valeur);
					$this->champs[$k]->record();
				}
			}
			//echo($sql);
			return $this->query($sql);
		}
		function add() {
			if (isset($this->champRefOrdre)) {
				$result=$this->query("SELECT  ordre from ".$this->fichier->nom." where ".$this->champRefOrdre->getEgalSQL()." order by ordre desc limit 0,1");
				$ordre=&$this->getChamp("ordre");
				$row=$result[0];
				if ($row[0]>0) {
					$ordre->setValeur($row[0]+1);
				} else {
					$ordre->setValeur(1);
				}
				//echo("ordre=".$ordre->getSQLString()."<br>");
			}
			$sql.="INSERT INTO ".$this->fichier->nom. " SET ";
			foreach($this->champs as $k=>$v) {
				if (!$this->champs[$k]->isFile()) {
					if ($sqlc!="") $sqlc.=", ";
					$sqlc.=$v->nom."=".$v->getSQLString();
				}
			}
			$sql.=$sqlc;
			$ret= $this->query($sql);
			$this->cle->setValeur($ret);
			foreach($this->champs as $k=>$v) {
				if ($this->champs[$k]->isFile()) {
					$this->champs[$k]->setValeur($ret);
					$this->champs[$k]->record();
				}
			}
			return $ret;
		}
		function delete() {
			if ($this->cle->valeur=="") {
				echo("Aucune fiche sélectionné !");
				return false;
			}
			$impossible=false;
			if (isset($this->liens)) {
				foreach($this->liens as $k=>$lien) {
					$lien->champ->setValeur($this->cle->valeur);
					$res=$lien->fiche->query("select count(*) from ".$lien->fiche->fichier->nom." where ".$lien->champ->getEgalSQL());
					$row=$res[0];
					if ($row[0]>0) {
						$impossible=true;
						echo("Impossible, il y a ".$row[0]." <b> ".$lien->fiche->fichier->nom."s </b><br>");
					}
				}
			} 
			if ($impossible==false) {
				foreach($this->champs as $k=>$v) {
					if ($this->champs[$k]->isFile()==true) {
						$this->champs[$k]->delete();
					}
				}
				//echo("DELETE FROM ".$this->fichier->nom." WHERE ".$this->cle->getEgalSQL());
				return $this->query("DELETE FROM ".$this->fichier->nom." WHERE ".$this->cle->getEgalSQL());
			}
		}
		function query($sql) {
			return $this->fichier->query($sql);
		}
		function read($cle) {
			$this->cle->setValeur($cle);
			$res=$this->query("SELECT * FROM ".$this->fichier->nom." WHERE ".$this->cle->getEgalSQL());
			$this->affecteRow($res[0]);
			
		}
		// affecte a partir d'une row sql
		// positionne la clé de l'éventuel fichier
		// JUSTE APRES LECTURE
		function affecteRow($row) {
			$cle=$row[$this->cle->nom];
			foreach($this->champs as $k=>$v) {
				$this->champs[$k]->affecteRow($row);
			}		
		}
		// affecte a partir des variables globales formulaire
		// prépare la clé de l'éventuel fichier
		// JUSTE AVANT VALIDATION FORMULAIRE
		function affecteForm() {
			$cle=$GLOBALS[$this->cle->getFormNom()];
			foreach($this->champs as $k=>$v) {
				$this->champs[$k]->affecteForm($cle);
			}		
		}		
		// affecte a partir des variables globales
		// prépare la clé de l'éventuel fichier
		// JUSTE AVANT NOUVEAU (AVANT NEW)
		function affecteGlobal() {
			foreach($this->champs as $k=>$v) {
				$this->champs[$k]->affecteGlobal();
			}		
		}
		// affecte pour nouveau
		// prépare la clé de l'éventuel fichier
		// JUSTE AVANT NOUVEAU (APRES GLOBAL)
		function affecteNew() {
			foreach($this->champs as $k=>$v) {
				$this->champs[$k]->affecteNew();
			}		
		}
		//renvoi un where avec filtres éventuels
		function setWhere($w) { $this->where=$w;}
		function getWhere() {
			return $this->where;
		}
		function getOrder() {
			if (isset($this->champRefOrdre)) {
				return "ORDER BY ordre";
			} else return "ORDER BY ".$this->label->nom;
		}
		function setChampSel(&$cs) { $this->champSel=&$cs;}
		function getChampSel() {
			if (!isset($this->champSel)) {
				if (!isset($this->label))
					$this->champSel[]=&$this->cle;
				$this->champSel[]=&$this->label;
			}
			return $this->champSel;
		}
		function setChampCherche(&$cc) { $this->champCherche=&$cc;}
		function getChampCherche() {
			if (!isset($this->champCherche)) {
				return $this->getChampSel();
			} else
				return $this->champCherche;
		}
	}
}
?>
