<?php
	if (!isset($c_fichier)) {
		$c_fichier=true;
		class DataBase {
			var $db_link;
			var $dbhost;
			var $baseurl;
			var $user;
			var $password;
			var $trace;
			var $journal; // nom du fichier journal des modifications
						// si vide pas de journal
			function DataBase($dbhost,$baseurl,$user,$password) {
				$this->dbhost=$dbhost;
				$this->baseurl=$baseurl;
				$this->user=$user;
				$this->password=$password;
			}
			function query($sql,$toclose=1) {
				GLOBAL $debug,$admin_email;
				//echo("<br>".$sql."<br>");
				if (!$this->db_link) {
					$this->db_link = mysql_connect($this->dbhost,$this->user,$this->password);
					mysql_select_db($this->baseurl,$this->db_link);
					$this->trace.="<br>Connexion";
					//echo("connexion $db_link<br>");
				}
				$this->trace.=" <br>$sql";
				if ($ok = mysql_query($sql)) {
					if (eregi("^select",$sql)) {
						$nb = mysql_num_rows($ok);
						if ($nb) {
							for($i=0; $i<$nb; $i++) {
								$out[$i] = mysql_fetch_array($ok);
							}
						} else $out=array();
					} else if (eregi("^insert",$sql)) {
						$this->journalise($sql);
						$out = mysql_insert_id();
					} else if (eregi("^update",$sql)) {
						$this->journalise($sql);
						$out = mysql_affected_rows();
					} else if (eregi("^delete",$sql)) {
						$this->journalise($sql);
						$out = mysql_affected_rows();
					}
					if ($toclose) $this->dbClose();
					return $out;
				} else {
					if ($debug) {
						echo("</table>$this->trace<br>".mysql_error());
					} else {
						echo "</table><b>Une erreur a eu lieu, un message à été envoyé au webmaster,<br>veuillez lui écrire en lui précisant la date, l'heure et les dernières opérations que vous avez effectuées.<br>Merci. <a href='mailto:$admin_email?subject='[scrabble] erreur'>$admin_email</a></b>";
						mail($admin_email,"[$nom_projet] Erreur SQL",str_replace("<br>","\n",$this->trace)."\n".mysql_error());
					}
					$this->dbClose();
					exit();
				}
			}
			
			function dbClose() {
				if ($this->db_link) {
					//echo("close<br>");
					$this->trace.="<br>close";
					mysql_close();
					mysql_close($this->db_link);
					$this->db_link="";
				}
			}
			function journalise($sql) {
				if ($this->journal=="") {
					return;
				}
				$fd=fopen($this->journal,"a");
				fwrite($fd,"#".date("d/m/Y H:i:s")."\n".$sql.";\n");
				fclose($fd);
			}
		}	
		class Fichier {
			var $nom;
			var $database;
			function Fichier($nom,$database) {
				$this->nom=$nom;
				$this->database=$database;
			}
			function query($sql,$toclose=1) {
				return($this->database->query($sql,$toclose));
			}
		}
	}
?>
