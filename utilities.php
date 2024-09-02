<?php

/**
 * @param mixed $subject
 *
 * @return bool - true (a representation of a float in a string or as a float itself), false (not a float representation)
 */
function str_isFloat($subject) {
    if ($subject === null) return false;
    return preg_match("/\d*(?:\.\d+)?/", $subject) === 1;
    // Note:  I could make this much more robust (i.e. accept actual floats, and filter out strings with alphas in them), but this will do for now.
}

/**
 * @param string $line
 *
 * @return array|false - Return false on failure, otherwise returns an associative array containing the data.
 */
function parseLine($line)
{
    if (gettype($line) !== "string") return false;

    $bits = explode("\t", $line);

    if (count($bits) < 16) return false;
    return [
        "NAME" => $bits[1],
        "TYPE" => $bits[2],
        "LOCATION_DESCRIPTION" => $bits[4],
        "ADDRESS" => $bits[5],
        "STATUS" => $bits[10],
        "FOOD_ITEMS" => strtolower($bits[11]),
        "LAT" => (float)$bits[14],
        "LON" => (float)$bits[15]
    ];

    // Note:  We are hard coding indexes here, which may not be good. If there is any change to the format of the data
    //        file produced by the City of San Francisco, then the indexing in this function will need to be revisited.
    //        It may actually be a good idea to have a validation function for the header line which could identify
    //        a column name change or column position change.

    // Note:  I would also prefer to code a class which you point to the flat file and all you need to do is call a
    //        single function to get the closest three.  The class would wrap away the parsing, etc.
}

/**
 * @param $latA - Latitude component of the first coordinate.
 * @param $lonA - Longitude component of the first coordinate.
 * @param $latB - Latitude component of the second coordinate.
 * @param $lonB - Longitude component of the second coordinate.
 * @return float - A value representing the distance.
 */
function getDistance($latA, $lonA, $latB, $lonB) {
    // Note:  This is not a true distance calculation, because lat and lon difference can be converted to actual
    //        distance values (measured in meters or miles), but you need to account for the curvature of the earth, and
    //        the fact that the distance between meridians narrows as your reach the poles.  The assumption being made
    //        in this function is that the points being compared are not far away from each other (they are all in the
    //        city of San Francisco, so good enough), that units of lattitude/longitude are substituted for units of
    //        distance on a grid, and the assumption is made that one unit of lattitude is equal to one unit of
    //        longitude.

    $latDelta = abs($latA - $latB);
    $lonDelta = abs($lonA - $lonB);

    return sqrt(($latDelta * $latDelta) + ($lonDelta * $lonDelta));
}

/**
 * A simple node class used for building a linked list.
 */
class Node {
    public $data;
    public $next;

    public function __construct($newData = null) {
        $this->data = $newData;
        $this->next = null;
    }
}

/**
 * @param $newNode - The node which may potentially be added to the list.
 * @param $matches - The head of the list to which the node may be added.
 *
 * @return Node - The node representing the head of the list which may be the new node.
 */
function closestAdd($newNode, $matches = null) {
    $listLimit = 3; // This could be made a const, but I prefer to keep the definition in scope.  May be best to move it to a configuration file, or make it settable in the UI, but for now it will be 3.
    $header = $matches;
    $iter = $matches;
    $count = 1;

    // Parameter checks
    if(
        $newNode === null ||
        !isset($newNode->data)
        // Note: The parameter matches could be a null or a node
    ) {
        return $header;
    }

    // Check first position to see if we have a new header...
    if ($iter === null || $newNode->data['DISTANCE'] < $iter->data['DISTANCE']) {
        $newNode->next = $header;
        $header = $newNode;
        $iter =  $newNode;
    }

    // ...and if we don't have a new header we need to look at the others if there are others...
    else if ($iter->next !== null) {
        // Loop to find a placement
        while($iter->next !== null && $count < $listLimit) {
            $previous = $iter;
            $iter = $iter->next;
            $count++;

            if ($newNode->data['DISTANCE'] < $iter->data['DISTANCE']) {
                $newNode->next = $iter;
                $previous->next = $newNode;
                $iter = $newNode;
                break;
            }
        }
    }

    // ...and if are no others then we can place at the end
    else {
        $iter->next = $newNode;
    }


    // Loop to the limit
    while($iter->next !== null && $count < $listLimit) {
        $iter = $iter->next;
        $count++;
    }

    // Drop the last item off the end of the list
    if ($count == $listLimit && $iter->next !== null) {
        unset($iter->next);
        $iter->next = null;
    }

    // No matter what happened, we return the header
    return $header;
}
