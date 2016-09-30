<?php
require_once('connect.php');
require_once ('pointLocation.class.php');

//This class is used to calculate distances in states and determine locations
class distanceCalculator extends pointLocation{
	private $db;
	//Google Maps API key
	public $apiKey;
	private $curl;

	function __construct($apiKey = ""){
		$this->curl = curl_init();
		$this->apiKey = $apiKey;
		$this->db = connect();
	}
	
	//Retrieves coordinates from the track_points table
	public function getPoints($offset = 0, $limit = 25){
		$q = 'SELECT CONCAT(lat," ",lon) as point FROM track_points ORDER BY id ASC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($q);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_STR);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	//Retrieves all points from the track_points table
	public function getAllPoints(){
		$q = 'SELECT CONCAT(lat," ",lon) as point FROM track_points ORDER BY id ASC';
        $stmt = $this->db->prepare($q);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}