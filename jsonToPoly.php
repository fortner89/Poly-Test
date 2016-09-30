<?php
$data = file_get_contents('states.json');
$data = json_decode($data, true);
$poly = array();
foreach ($data as $key => $value) {
	echo "<h1>$key</h1>";
	for ($i=0; $i < count($data[$key]["Coordinates"]); $i++) { 
		/*echo $data[$key]["Coordinates"][$i]["lat"].", ".$data[$key]["Coordinates"][$i]["lng"]."<br/>";*/
		$poly[$key][] = $data[$key]["Coordinates"][$i]["lat"]." ".$data[$key]["Coordinates"][$i]["lng"];
	}
}

echo "<pre>";
var_dump($poly);
echo "</pre>";
$json = json_encode($poly);
file_put_contents("states-poly.json", $json);