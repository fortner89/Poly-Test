<?php
require_once('connect.php');

//This class is used to calculate distances in states and determine locations
class distanceCalculator{
	private $db;
	//Google Maps API key
	public $apiKey;
	private $curl;

	function __construct(){
		$this->db = connect();
	}
	
	//Retrieves coordinates from the track_points table
	public function getPoints($offset = 0, $limit = 25){
		$q = 'SELECT lat, lon FROM track_points ORDER BY id ASC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($q);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_STR);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	//Retrieves all points from the track_points table
	public function getAllPoints(){
		$q = 'SELECT lat, lon FROM track_points ORDER BY id ASC';
        $stmt = $this->db->prepare($q);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	//Inserts state geometry into the DB
	public function insertState($name, $shape){
		$q = 'INSERT INTO states (state_shape, state_name) VALUES (:shape, :name)';
        $stmt = $this->db->prepare($q);
        $stmt->execute(array(
        	':shape' => $shape,
        	':name' => $name
        ));
	}

	//Retrieves all states from DB
	public function getAllStates(){
		$q = 'SELECT * FROM states';
        $stmt = $this->db->prepare($q);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	//Retrieves a specific state from DB
	public function getState($id){
		$q = 'SELECT * FROM states where state_id = :id';
        $stmt = $this->db->prepare($q);
        $stmt->execute(array(
        	':id' => $id
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	//Calculates the total distance that has been traveled through each state
	public function calcStateDistance($points, $unit = "mi", $displayResult = 0){
		//Retrieve state shapes that have been converted into a usable format in geoPHP
		$stateShapes = $this->primeStateShapes();
		//This variable is used to measure the distance between this point and the current one
		$lastPoint = "";
		//Stores the total distance for each state
		$totals = array();

		//Loop through each point
		for ($i=0; $i < count($points); $i++) { 
			//Convert point into format that can determine its presence in a state
			$point = geoPHP::load("POINT(".$points[$i]['lon']." ".$points[$i]['lat'].")");
			//Loop through all state shapes trying to find the point
			for ($y=0; $y < count($stateShapes); $y++) { 
				if($stateShapes[$y]['shape']->contains($point)){
					//Prep $lastPoint
					if($lastPoint == ""){
						$lastPoint = $points[$i];
						break;
					}
					else{
						//Gather totals
						if(isset($totals[$stateShapes[$y]['name']])){
							$totals[$stateShapes[$y]['name']] += $this->hDistance($lastPoint['lat'], $lastPoint['lon'], $points[$i]['lat'], $points[$i]['lon']);
						}
						//Initialize index
						else{
							$totals[$stateShapes[$y]['name']] = $this->hDistance($lastPoint['lat'], $lastPoint['lon'], $points[$i]['lat'], $points[$i]['lon']);
						}
						$lastPoint = $points[$i];
						break;
					}
				}
			}
		}

		//Sets the unit type
		if($unit == "mi"){
			$totals = $this->metersToMiles($totals);
		}
		elseif ($unit == "km") {
			$totals = $this->metersToKilometers($totals);
		}
		
		//Echos formatted result if requested
		if($displayResult === 1){
			echo $this->displayStateDistance($totals, $unit);
		}
		
		//Returns array of totals
		return $totals;
	}

	//Haversine great circle distance formula for determining distance between points
	public function hDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
	{
	  // convert from degrees to radians
	  $latFrom = deg2rad($latitudeFrom);
	  $lonFrom = deg2rad($longitudeFrom);
	  $latTo = deg2rad($latitudeTo);
	  $lonTo = deg2rad($longitudeTo);

	  $latDelta = $latTo - $latFrom;
	  $lonDelta = $lonTo - $lonFrom;

	  $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
	    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
	  return $angle * $earthRadius;
	}

	//This converts the state shapes into a format usable by geoPHP
	private function primeStateShapes(){
		$states = $this->getAllStates();
		$stateShapes = array();
		for ($i=0; $i < count($states); $i++) { 
			$stateShapes[$i]['name'] = $states[$i]['state_name'];
			$stateShapes[$i]['shape'] = geoPHP::load($states[$i]['state_shape'], 'wkb');
		}
		return $stateShapes;
	}

	//Converts meters to miles from the $totals array
	private function metersToMiles($data){
		foreach ($data as $key => $value) {
			$data[$key] = round($value / 1609.344, 2);
		}
		return $data;
	}

	//Converts meters to kilometers from the $totals array
	private function metersToKilometers($data){
		foreach ($data as $key => $value) {
			$data[$key] = round($value / 1000, 2);
		}
		return $data;
	}

	//This is used to display formatted results for calcStateDistance
	//Options for $unit are "mi" and "km".
	private function displayStateDistance($data, $unit = "mi"){
		switch ($unit) {
			case 'mi':
				$unit = " miles";
				break;
			case 'km':
				$unit = " kilometers";
				break;
			default:
				return "Invalid unit paramter";
				break;
		}
		$html = "
		<table>
			<thead>
				<tr>
					<th>State</th>
					<th>Distance (".$unit.")</th>
				</tr>
			</thead>
			<tbody";
		foreach ($data as $key => $value) {
			$html .= "
				<tr>
					<td>".$key."</td>
					<td>".$value."</td>
				</tr>";
		}
		$html .= "
			</tbody>
		</table>";
		
		return $html;
	}
}