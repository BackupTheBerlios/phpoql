<?php

//zones
if (!isset($c_zone)) {
	$c_zone=true;
	class Zone {
		var $nom;
		function getFormNom() {
			return "form_".$this->nom;
		}
	}
	class ZonePage extends Zone{
		var $fic;
		function ZonePage($nom,$fic) {
			$this->nom=$nom;
			$this->fic=$fic;
		}
		function getFormInput() {
			$out=("<h4>".$this->nom."</<h4>");
			$out.=("<textarea wrap cols=80 rows=10 name='".$this->getFormNom()."'>");
			if (file_exists($this->fic)) {
				$fd=fopen($this->fic,"r");
				$ch=fread($fd,filesize($this->fic));
				$out.=$ch;
				fclose($fd);
			}
			$out.=("</textarea>");
			return $out;
		}
		function record() {
			if ($GLOBALS[$this->getFormNom()]!="") {
				$fd=fopen($this->fic,"w");
				fwrite($fd,stripslashes($GLOBALS[$this->getFormNom()]));
				fclose($fd);
				echo("<h4>".$this->nom." enregistré</h4>");
			}
		}
	}
}
?>
