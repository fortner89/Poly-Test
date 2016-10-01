<?php
require_once('classes/geoPHP.inc');
require_once('classes/distanceCalculator.class.php');
$calc = new distanceCalculator();

$points = $calc->getAllPoints();
$calc->calcStateDistance($points, 'mi', 1);