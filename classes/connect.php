<?php
function connect(){
	try {
	    $db = new \PDO("mysql:host=127.0.0.1;dbname=blue_ink_test;charset=utf8", "root", "");
	    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	    $db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
	    return $db;
	} catch (\PDOException $e) {
	    echo $e->getMessage() . "\n";
	    echo "Database error.";
	    die();
	}
}