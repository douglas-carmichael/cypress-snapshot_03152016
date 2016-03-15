<?php

/* Project */
        define('PROJECT_VERSION', 'Cypress Payment System');

/* Database */
        define('DB_SERVER', '127.0.0.1');
        define('DB_USER', 'cypress');
        define('DB_PASS', 'bapy-thi-sy-cu');
        define('DB_DATABASE', 'cypress_production');
        define('DB_TYPE', 'pgsql');
        define('DB_PORT', '5432');

/* Simulator Communication */
		define('SHARED_SECRET', 'fQxywNjFTx06Ll');
		define('DEBUG_UUID', '71610693-8e53-4f95-9ea5-82c7ec435d4b');

/* Downloads */
		define('DOWNLOAD_BASE', 'http://www.dcarmichael.net:8080/download.php');
		
		/* If set to nonempty value (Example: example.com) will only allow downloads
			when referrer contains this text */
		define('ALLOWED_REFERRER', '');

/* Uploads */
		define('UPLOAD_BASE', '/home/tgerber/uploads/');

?>
