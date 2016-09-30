<?php
require_once ('classes/distanceCalculator.class.php');
$data = file_get_contents('states-poly.json');
$data = json_decode($data);
/*echo "<pre>";
var_dump($data);
echo "</pre>";*/
$pointLocation = new distanceCalculator();
$res = $pointLocation->getPoints(0, 4000);
//$res = $pointLocation->getAllPoints();

/*$points = array("50 70","70 40","-20 30","100 10","-10 -10","40 -20","110 -20");
$polygon = array("-50 30","50 70","100 50","80 10","110 -10","110 -30","-20 -50","-30 -40","10 -10","-10 10","-30 -20","-50 30");
// The last point's coordinates must be the same as the first one's, to "close the loop"
foreach($points as $key => $point) {
    echo "point " . ($key+1) . " ($point): " . $pointLocation->pointInPolygon($point, $polygon) . "<br>";
}*/

/*echo "<pre>";
var_dump($res);
echo "</pre>";*/

foreach ($res as $key => $point) {
	foreach ($data as $state => $polygon) {
		$status = $pointLocation->pointInPolygon($point['point'], $polygon);
		/*echo "<h1>$state</h1><pre>";
		var_dump($polygon);
		echo "</pre>";*/
		if($status == "inside"){
			//echo $point['point']." inside ".$state."<br/>";
		}
	}
}
echo "done";