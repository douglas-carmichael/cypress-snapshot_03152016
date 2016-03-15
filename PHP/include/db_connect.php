<?php
			try {
			    $ourDBHandler = new PDO(DB_TYPE . ':host= ' . DB_SERVER . ';port=' 
			    . DB_PORT . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
					} catch (PDOException $ourPDOException) {
				print "Error!: " . $ourPDOException->getMessage() . "<br/>";
				die();
				}
			$ourDBHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


?>
