
<?php
//champ
if (!isset($c_champ)) {
	$c_champ=true;
	class Champ {
		var $nom;
		var $label;
		var $taille;
		var $type;
		var $valeur;
		var $lock;
		var $vidable=true;
		var $fiche;
		function Champ($nom,$taille,$type) {
			$this->nom=$nom;
			$this->label=$nom;
			$this->taille=$taille;
			$this->type=$type;
		}
		function setFiche($f) { $this->fiche=$f;} // mis à jour en ajout de champ a une fiche
		function setVidable($v) { $this->vidable=$v;}
		function isVidable() { return $this->vidable;}
		function setString($v) { $this->valeur=$v;}
		function setValeur($v) { $this->valeur=$v;}
		function getValeur() { return $this->valeur;}
		function getString() { return $this->valeur;}
		function getStringHuman() { return $this->getString();}
		function getURL() { return $this->nom."=".$this->getString();}
		function setLock($t) { $this->lock=$t;}
		function getFormInput() {
			if ($this->lock==true) {
				return "<td>".$this->label."&nbsp;:&nbsp;</td><td>".$this->getString()."</td>"
					."<input type='hidden' name=".$this->getFormNom()." value=\"".htmlentities($this->getString())."\"'>";
			} else {
				return "<td>".$this->label."&nbsp;:&nbsp;</td><td><input type='text' name=".$this->getFormNom()." size=".$this->taille." value=\"".htmlentities($this->getString())."\"'></td>";
			}
		}
		function getFormNom() { return "form_".$this->nom;}
		function getSQLString() {return $this->getString();}
		function getEgalSQL() {return $this->nom."=".$this->getSQLString();}
		function isValide() {return "";} // AVANT ENREGISTREMENT APRES AFFECTEFORM
		function isFile() { return false;}
		function affecteRow($row) {$this->setValeur(stripslashes($row[$this->nom]));} // JUSTE APRES LECTURE
		function affecteForm() {$this->setString(stripslashes($GLOBALS[$this->getFormNom()]));} // JUSTE APRES FORM
		function affecteGlobal() {$this->setString(stripslashes($GLOBALS[$this->nom]));} // JUSTE AVANT NOUVEAU
		function affecteNew() {} // JUSTE AVANT NOUVEAU
	}
	class ChampFile extends Champ {
		var $path;
		var $url;
		var $prename;
		var $postname;
		function ChampFile($nom,$url,$path,$prename,$postname) {
			$this->nom=$nom;
			$this->label=$nom;
			$this->path=$path;
			$this->url=$url;
			$this->prename=$prename;
			$this->postname=$postname;
		}
		function isFile() { return true;}
		function affecteRow($row) {
			$this->setString($this->fiche->cle->getString());
		}
		function affecteGlobal() {
			$this->setString($this->fiche->cle->getString());
		}
		function record() {
			$fichier=$GLOBALS[$this->getFormNom()];
			$fichier_raz=$GLOBALS["raz_".$this->getFormNom()];
			if ($fichier_raz=='1') {
				echo("<h1>delete</h1>");
				$this->delete();
				return;
			}
			if ($fichier=="" || $fichier=="none") {
				return;
			}
			if (!copy($fichier,$this->getPath()))
				echo "Impossible de récupérer <b>".$this->label."</b>";
			else
				echo "Fichier <b>".$this->label."</b> enregistré";
		}
		function delete() {
			if (file_exists($this->getPath())) {
				if (!unlink($this->getPath()))
					echo $this->getPath()." Impossible de supprimer <b>".$this->label."</b>";
				else
					echo "Fichier <b>".$this->label."</b> supprimé";
			}
		}
		function getLien() {
			if (file_exists($this->getPath())) {
				return ("<a target=_blank href='".$this->getURL()."'>".$this->label."</a>");
			} else {
				return ("Aucun fichier");
			}
		}
		function getURL() {
			return $this->url."/".$this->prename.$this->getString().$this->postname;	
		}
		function getPath() {
			return $this->path."/".$this->prename.$this->getString().$this->postname;	
		}
		function getFormInput() {
			if ($this->lock==true) {
				return "<td>".$this->label."&nbsp;:&nbsp;</td>"
					."<td>".$this->getLien()."</td>";
			} else {
				return ("<td>".$this->label."&nbsp;:&nbsp;</td>".
				"<td><input type='file' name='".$this->getFormNom()."' size=32>"
				." Efface : <input type='checkbox' name='raz_".$this->getFormNom()."' value='1'></input> / "
				."Visualise : ".$this->getLien().
				"</td>");
			}
		}
	}
	class ChampImage extends ChampFile {
		function getLien() {
			return ("<img src='".$this->getURL()."'>");
		}
		function getFormInput() {
			if ($this->lock==true) {
				return "<td valign=center>".$this->label."&nbsp;:&nbsp;</td><td>".$this->getLien()."</td>";
			} else {
				return ("<td valign=bottom>".$this->label."&nbsp;:&nbsp;</td>".
				"<td valign=center><input type='file' name='".$this->getFormNom()."' size=32>".$this->getLien().
				"</td>");
			}
		}
	}
	class ChampString extends Champ {
		function ChampString ($nom,$taille) {
			Champ::Champ($nom,$taille,"CHAR");
		}
		function getSQLString() {
			return "'".addslashes($this->valeur)."'";
		}
	}
	class ChampInt extends Champ {
		function ChampInt ($nom) {
			Champ::Champ($nom,11,"INT");
		}
		function getSQLString() {
			if ($this->valeur=="") return 0;
			else return $this->valeur;
		}
	}
	class ChampBoolean extends Champ {
		function ChampBoolean ($nom) {
			Champ::Champ($nom,1,"TINYINT");
		}
		function getSQLString() {
			if ($this->valeur=="") return 0;
			else return $this->valeur;
		}
		function getFormInput() {
			if ($this->lock==true) {
				$c="<td>".$this->label."&nbsp;:&nbsp;</td><td>";
				if ($this->valeur==1) $c.="Oui"; else $c.="Non";
				$c.="</td>";
			} else {
				if ($this->valeur==1) {
					$checked=" checked ";
				}
				$c="<td>".$this->label."&nbsp;:&nbsp;</td><td><input $enable type=checkbox name=".$this->getFormNom()." value='1' $checked></td>";
			}
			return $c;
		}
	}
	class ChampOrdre extends ChampInt {
		function ChampOrdre() {
			ChampInt::ChampInt("ordre");
			$this->setLock(true);
		}
	}
	class ChampFloat extends Champ {
		function ChampFloat ($nom) {
			Champ::Champ($nom,11,"FLOAT");
		}
		function getSQLString() {
			if ($this->valeur=="") return 0;
			else return $this->valeur;
		}
		function setString($st) {
			$st=strtr($st,",",".");
			Champ::setString($st);
		}
	}
	class ChampColor extends ChampInt {
		function getFormInput() {
			if ($this->lock==true) {
				return "<td>".$this->label."&nbsp;:&nbsp;</td><td bgcolor=".$this->getColorHTML().">".$this->getHexa()."</td>"
					."<input type='hidden' name=".$this->getFormNom()." value=\"".htmlentities($this->getHexa())."\"'>";
			} else {
				return "\n<td>".$this->label."&nbsp;:&nbsp;</td><td bgcolor=".$this->getColorHTML().">"
					."<font color='".$this->getColorHTMLInv()."'> (RRVVBB) </font><input type='text' name=".$this->getFormNom()." size=".$this->taille." value=".$this->getHexa()."></td>\n";
			}
		}
		function getHexa() {
			return sprintf("%06X",$this->valeur);
		}
		function getColorHTML() {
			return '#'.$this->getHexa();
		}
		function getColorHTMLInv() {
			return '#'.sprintf("%06X",16777215^$this->valeur);
		}
		function affecteForm() {
			$this->valeur=hexdec($GLOBALS[$this->getFormNom()]);
		}
	}
	class ChampText extends ChampString {
		var $col;
		var $row;
		function ChampText($nom,$col,$row) {
			$this->col=$col;
			$this->row=$row;
			Champ::Champ($nom,-1,"TEXT");
		}
		function getFormInput() {
			if ($this->lock==true) {
				return "<td valign=top>".$this->label." : </td><td>$this->valeur</td>\n";
			} else {
				return "<td valign=top>".$this->label." : </td><td><textarea name=".$this->getFormNom()." cols=".$this->col." rows=".$this->row." wrap >".htmlentities($this->valeur). "</textarea></td>\n";
			}
		}		
	}
	class ChampDate extends ChampString {
		var $er;
		function ChampDate($nom) {
			Champ::Champ($nom,16,"DATE");
		}
		function getString() {
			return $this->dteSQL2fr($this->valeur);
		}
		function setString($str) {
			$this->setValeur($this->dteFr2SQL($str));
			if ($str!="" && $this->valeur=="") {
				$this->er=$str;
			}
		}
		function isValide() {
			return $this->er;
		}
		// conversion d'une date SQL vers une date FR
		// renvoi une chaine vide si erreur
		function dteSQL2fr($dtesql) {
			$y=substr($dtesql,0,4);
			$m=substr($dtesql,5,2);
			$d=substr($dtesql,8,2);
			if (checkdate($m,$d,$y)==false) {
				return "";
			}
			return ("$d/$m/$y");
		}
		// conversion d'une date FR d/m/y vers SQL YYYY-MM-DD
		function dteFr2SQL($dtefr) {
			$dt=explode("/",$dtefr);
			if (count($dt)!=3) return "";
			$d=$dt[0];
			$m=$dt[1];
			$y=$dt[2];
			if (checkdate($m,$d,$y)==false) {
				return "";
			}
			return ("$y-$m-$d");
		}
		// conversion d'une date SQL vers une date timestamp
		function dteSQL2stamp($dtesql) {
			$y=substr($dtesql,0,4);
			$m=substr($dtesql,5,2);
			$d=substr($dtesql,8,2);
			return mktime(0,0,0,$m,$d,$y);
		}
		// renvoi la date d'aujourd'hui en fr
		function todayFr() {
			return date("d/m/Y");
		}
	}
	class ChampDateModif extends ChampDate {
		function ChampDateModif($nom) {
			ChampDate::ChampDate($nom);
			$this->setLock(true);
		}		
		function affecteGlobal() {
			$this->setString($this->todayFr());
		}
	}
	class ChampDateCreation extends ChampDate {
		function ChampDateCreation($nom) {
			ChampDate::ChampDate($nom);
			$this->setLock(true);
		}		
		function affecteNouveau() {
			$this->setString($this->todayFr());
		}		
	}
	class ChampRefComboString extends ChampString {
		var $ficheLien;
		var $filtre;
		// nom + fiche lien + nom du champ filtre
		function ChampRefComboString($taille,$nom,$ch,$filtre="") {
			Champ::Champ($nom,$taille,"CHAR");
			$this->ficheLien=$ch;
			$this->setVidable(false);
			$this->filtre=$filtre;
			
		}
		function getStringHuman() {
			$v=$this->valeur;
			$this->ficheLien->read($v);
			return $this->ficheLien->label->getString();
		}
		function getFormInput() {
			if ($this->lock==true) {
				// lecture de la fiche liée en fonction de la clé
				$this->ficheLien->read($this->valeur);
				return "<td valign=top>".$this->label." : </td><td>"
					."<input type=hidden name=".$this->getFormNom()." value=".$this->getString().">".$this->ficheLien->label->getString()."</td>\n";
			} else {
				$out="<td valign=top>".$this->label." : </td><td>";
				$out.="<select name=".$this->getFormNom().">";
				if ($this->isVidable()) {
					$out.="<option value=0>--- Sans ---</option>";
				}
				// lecture des fiches
				$res=$this->ficheLien->query("SELECT ".$this->ficheLien->cle->nom.",".$this->ficheLien->label->nom." FROM ".$this->ficheLien->fichier->nom." ".$this->getWhereFiltre()." ORDER BY ".$this->ficheLien->label->nom);
				foreach($res as $v) {
					$ref=$v[$this->ficheLien->cle->nom];
					if ($ref==$this->valeur) $sel="selected"; else $sel="";
					$out.="<option value=".$ref." $sel>".$v[$this->ficheLien->label->nom]."</option>";
				}
				$out.="</select>";
				$out.="</td>\n";
				return $out;
			}
		}		
		function getWhereFiltre () {
			$gl=$GLOBALS[$this->filtre];
			if ($gl!="") {
				return " WHERE $this->filtre=".$gl;
			}
		}
	}
	class ChampRefCombo extends ChampInt {
		var $ficheLien;
		var $filtre;
		// nom + fiche lien + nom du champ filtre
		function ChampRefCombo($nom,$ch,$filtre="") {
			Champ::Champ($nom,11,"INT");
			$this->ficheLien=$ch;
			$this->setVidable(false);
			$this->filtre=$filtre;
			
		}
		function getStringHuman() {
			$this->ficheLien->read($this->valeur);
			return $this->ficheLien->label->getString();
		}
		function getFormInput() {
			if ($this->lock==true) {
				// lecture de la fiche liée en fonction de la clé
				$this->ficheLien->read($this->valeur);
				return "<td valign=top>".$this->label." : </td><td>"
					."<input type=hidden name=".$this->getFormNom()." value=".$this->getString().">".$this->ficheLien->label->getString()."</td>\n";
			} else {
				$out="<td valign=top>".$this->label." : </td><td>";
				$out.="<select name=".$this->getFormNom().">";
				if ($this->isVidable()) {
					$out.="<option value=0>--- Sans ---</option>";
				}
				// lecture des fiches
				$res=$this->ficheLien->query("SELECT ".$this->ficheLien->cle->nom.",".$this->ficheLien->label->nom." FROM ".$this->ficheLien->fichier->nom." ".$this->getWhereFiltre()." ORDER BY ".$this->ficheLien->label->nom);
				foreach($res as $v) {
					$ref=$v[$this->ficheLien->cle->nom];
					if ($ref==$this->valeur) $sel="selected"; else $sel="";
					$out.="<option value=".$ref." $sel>".$v[$this->ficheLien->label->nom]."</option>";
				}
				$out.="</select>";
				$out.="</td>\n";
				return $out;
			}
		}		
		function getWhereFiltre () {
			$gl=$GLOBALS[$this->filtre];
			if ($gl!="") {
				return " WHERE $this->filtre=".$gl;
			}
		}
	}
	
		
}
?>
