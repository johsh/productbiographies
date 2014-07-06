<?php
//LOCAL
if (!$link = mysql_connect('localhost', 'root', 'root')) {
	    echo 'Keine Verbindung zu mysql';
	    exit;
	}

	if (!mysql_select_db('produktbiographien', $link)) {
	    echo 'Konnte Schema nicht selektieren';
	    exit;
	}

/*
	## INPUT DATA ##
	## FROM INDEX.PHP ##
*/
	
	$set_table = addslashes($_GET['data_table']);
	$set_domain = addslashes($_GET['domain']);
	$set_item = addslashes($_GET['item']);
	$set_element = addslashes($_GET['element']);
	$set_year = 2011;
	
	
/*	
	####################
	## BEGIN REQUESTS ##
	####################
*/


/*	
	REQUEST: COLOR COUNTRY
	------------------
*/
	$color_array = array();
	$sql_color = "SELECT Country, Value, Element, Item FROM $set_table WHERE Domain ='$set_domain' AND Item = '$set_item' AND Year = '$set_year'";
	
	$result_color = mysql_query($sql_color, $link);

		if (!$result_color) {
		    echo "DB Fehler, konnte die Datenbank nicht abfragen\n";
		    echo 'MySQL Error: ' . mysql_error();
		    exit;
		}


	while ($row = mysql_fetch_assoc($result_color)) {
		$element = $row['Element'];
		$element_array = explode(" ", $element);

		if(in_array($set_element, $element_array)){
			//is "Export" in "Export Quantity (1000 Head)"?
			$country = $row['Country'];
			$value = $row['Value']; 
				if(in_array("(1000", $element_array)){
					$value = $value * 1000;	
				}
			$c = array(
				"Country" => $country,
				"Value" => $value
			);

			$color_array[] = $c;
			}
	}



	function adjust_value($element_to_filter, $element_value, $index){
		/*
		value * 1000 if necessary
		*/
		$element_array = explode(" ", $element_to_filter);
		$unit = $element_array[$index];
		if($unit == "(1000" ){
			$element_value = $element_value * 1000;					
		}
		return $element_value;									
	}
	

	function filter_trade_mode($element_to_filter, $requested_trade_mode){
		/*
		Checks for the requested trade-mode (import/export) and returns boolean
		*/
		$element_trade_mode = strtolower($element_to_filter); //export quantity (1000 head)
		$element_array = explode(" ", $element_trade_mode);
		$element_trade_mode = $element_array[0];
		$requested_trade_mode = strtolower($requested_trade_mode); //export
		
		return ($element_trade_mode == $requested_trade_mode);

	}

	function distance($latA, $lonA, $latB, $lonB)
	{
		//TAKEN FROM http://derickrethans.nl/spatial-indexes-calculating-distance.html
        // convert from degrees to radians
        $latA = deg2rad($latA); $lonA = deg2rad($lonA);
        $latB = deg2rad($latB); $lonB = deg2rad($lonB);

        // calculate absolute difference for latitude and longitude
        $dLat = ($latA - $latB);
        $dLon = ($lonA - $lonB);

        // do trigonometry magic
        $d =
                sin($dLat/2) * sin($dLat/2) +
                cos($latA) * cos($latB) * sin($dLon/2) *sin($dLon/2);
        $d = 2 * asin(sqrt($d));
        return $d * 6371;
	}

	function checkRequestForError($this_result){
		if (!$this_result) {
		    echo "DB Fehler, konnte die Datenbank nicht abfragen\n";
		    echo 'MySQL Error: ' . mysql_error();
		    exit;
		}
	}

	$total_reporters = $total_distance = $total_value = 0;
	foreach ($all_travel_data as $key => $value) {
 		//echo "**".$value["reporter"] . "**: " . round($value["dist"]) . "km *(dist)*; " . round($value["value"]). " chickens *(trade)* <br/>";
		$total_reporters++;
		$total_distance += $value["dist"];
		$total_value += $value["value"];
	};

	$average_dist = $total_distance/$total_reporters;

	$total_import = $cattle_import + $chickens_import + $pigs_import;
	
	$a = array(
	"import_value" => $total_import,
	"item" => $this_item,
	"country" => $set_country,
	"year" => $set_year,
	"population" => $this_population,
	"cattle_import" => $cattle_import,
	"cattle_export" => $cattle_export,
	"chickens_import" => $chickens_import,
	"chickens_export" => $chickens_export,
	"pigs_import" => $pigs_import,
	"pigs_export" => $pigs_export,
	"cattle_production" => $cattle_production,
	"chickens_production" => $chickens_production,
	"pigs_production" => $pigs_production,
	"average_dist" => $average_dist

		);

	//echo json_encode($a);	
	$d = array(
		"data" => $color_array
		);
	echo json_encode($color_array);	

?>