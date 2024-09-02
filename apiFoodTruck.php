<?php
//header('Access-Control-Allow-Origin', '*');
//header('Access-Control-Allow-Methods', 'GET');
include_once "utilities.php";


// Extract the parameters
$url = $_SERVER['REQUEST_URI'];
$url_components = parse_url($url);
parse_str($url_components['query'], $params);

$want = strtolower(trim($params['want'] ?? ""));
$lat  = trim($params['lat'] ?? "");
$lon  = trim($params['lon'] ?? "");


// Validate inputs
$errors = [];
if ($want == "") $errors[] = "Missing parameter (want)"; // Note:  Intentionally using == here
if ($lat == "") $errors[] = "Missing parameter (lat)";
if ($lon == "") $errors[] = "Missing parameter (lon)";
$lat = trim($lat);
if (str_isFloat($lat)) {
    $lat = (float)$lat;
} else {
    $errors[] = "Parameter (lat) invalid";
}
$lon = trim($lon);
if (str_isFloat($lon)) {
    $lon = (float)$lon;
} else {
    $errors[] = "Parameter (lon) invalid";
}
// Note:  A class or function for validating inputs may have been appropriate here.  Such a function could also
//        be used to check for injections.

// If there was something wrong with the parameters let the caller know and go no further
if (count($errors)) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(400);
    echo '{"status": "failure", "error": "' . implode(", ", $errors) . '"}';
    exit();
}


// Open the file to prepare to scan
$file = "Mobile_Food_Facility_Permit.txt";
$fd = fopen($file, 'r');
if ($fd === false) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo '{"status": "failure", "error": "Internal failure"}';
    exit();
}
fseek($fd, 0, SEEK_END);
$end = ftell($fd);
fseek($fd, 0);
$ignoreHeader = fgets($fd);


// Scan the list
$matches = null;
$others = null;
while (ftell($fd) < $end) {
    $line = fgets($fd);
    $data = parseLine($line);
    $data['DISTANCE'] = getDistance($lat, $lon, $data['LAT'], $data['LON']);

    // Only consider approved locations.  We assume all others are considered unsafe for the public.
    if ($data['STATUS'] === "APPROVED") {
        $newNode = new Node($data);

        // If the list of food items contains our want string then we consider that a possible match...
        if (strpos($data['FOOD_ITEMS'], $want) !== false) {
            $matches = closestAdd($newNode, $matches);
        }

        // ...but if the items list doesn't have our want, then consider as a possible other option.
        else {
            $others = closestAdd($newNode, $others);
        }
    }
}


// Compile the package to be returned
$return = [];
$return['status'] = "success";

$return['matches'] = [];
$iter = $matches;
while ($iter !== null) {
    unset($iter->data['DISTANCE']);
    $return['matches'][] = $iter->data;
    $iter = $iter->next;
}

$return['others'] = [];
$iter = $others;
while ($iter !== null) {
    unset($iter->data['DISTANCE']);
    $return['others'][] = $iter->data;
    $iter = $iter->next;
}


// The options have been compiled, return them to the call as JSON
header('Content-Type: application/json; charset=utf-8');
http_response_code(200); // Note needed, but including for consistency here
echo json_encode((object)$return);