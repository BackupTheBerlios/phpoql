<?php

//page zone
if (!isset($c_pagezone)) {
	$c_pagezone=true;
	class PageZone {
		var $zones;
		var $papa;
		function PageZone($papa) {
			$this->papa=$papa;
		}
		function addZone(&$zn) {
			$this->zones[$zn->nom]=&$zn;
		}
		function affiche() {
			echo("<form action='".$this->papa."' method=post>");
			echo("<input type=submit name='pagezonerecord' value='Enregistrer'>");
			foreach($this->zones as $k=>$zone) {
				echo($zone->getFormInput());
			}
			echo("<input type=submit name='pagezonerecord' value='Enregistrer'>");
			echo("</form>");
		}
		function record() {
			if ($GLOBALS["pagezonerecord"]!="") {
				foreach($this->zones as $k=>$zone) {
					$zone->record();
				}			
			}
		}
	}
}
?>
