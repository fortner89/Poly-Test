<?php
function connect(){
	try {
	    $db = new \PDO("mysql:host=;dbname=;charset=utf8", "", "");
	    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	    $db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
	    return $db;
	} catch (\PDOException $e) {
	    echo $e->getMessage() . "\n";
	    echo "Database error.";
	    die();
	}
}