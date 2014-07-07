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
	$set_country = addslashes($_GET['country']);
	$set_year = 2011;
	
	$cattle_import = $chickens_import = $pigs_import
	= $cattle_export = $chickens_export = $pigs_export 
	= $cattle_production = $chickens_production = $pigs_production
	= $chicken_price
	= 0;

	$all_travel_data = array(); //get amount of partner countries	
	$all_travel_import = array(); // get average import km
	$all_travel_export = array(); // get average export km


	/*
	$showCattle = $_GET['showCattle'];
	if($showCattle == true){
		$set_cattle = "Cattle";
	};
	$showChickens = $_GET['showChickens'];
	$showPigs = $_GET['showPigs'];

	function setAnimal(){

	}
*/
/*
	## REQUEST CLAUSES ##
*/
	//$priority_clause = "(priority1=" . $set_priority1 . " OR priority6=" . $set_priority1.")";
	//$animal_clause = "(Item=" . $set_cattle . " OR Item=" . $set_chickens. " OR Item=" . $set_pigs.")";
	//$animal_clause = "(Item = 'Cattle' OR Item = 'Chickens' OR Item = 'Pigs')";


/*	
	####################
	## BEGIN REQUESTS ##
	####################
*/

/*	
	REQUEST: TRADE
	------------------
*/
	$sql_trade = "SELECT Country, Value, Item, Element FROM produktbiographien_trade WHERE Country = '$set_country' && Year = '$set_year'";//Item = 'Chickens'";


	$result_trade = mysql_query($sql_trade, $link);

	if (!$result_trade) {
	    echo "DB Fehler, konnte die Datenbank nicht abfragen\n";
	    echo 'MySQL Error: ' . mysql_error();
	    exit;
	}

	while ($row = mysql_fetch_assoc($result_trade)) {
/*
		## PROCESSING REQUESTED VALUES ##
*/
		$this_element = $row['Element']; 
		$this_item = $row['Item']; 
		$this_value = $row['Value'];

		if($this_item == "Cattle"){
			if( filter_trade_mode($this_element, "export") ){
				$cattle_export = adjust_value($this_element, $this_value, 2);
			}else if( filter_trade_mode($this_element, "import") ){
				$cattle_import = adjust_value($this_element, $this_value, 2);
			}
		}

		if($this_item == "Chickens"){
			if( filter_trade_mode($this_element, "export") ){
				$chickens_export = adjust_value($this_element, $this_value, 2);
			}else if( filter_trade_mode($this_element, "import") ){
				$chickens_import = adjust_value($this_element, $this_value, 2);
			}
		}

		if($this_item == "Pigs"){
			if( filter_trade_mode($this_element, "export") ){
				$pigs_export = adjust_value($this_element, $this_value, 2);
			}else if( filter_trade_mode($this_element, "import") ){
				$pigs_import = adjust_value($this_element, $this_value, 2);
			}
		}
		
	}




/*	
	REQUEST: POPULATION
	------------------
*/
	$sql_population = "SELECT Country, Value FROM produktbiographien_population WHERE Country = '$set_country' && Year = '$set_year'";
	
	$result_population = mysql_query($sql_population, $link);

		if (!$result_population) {
		    echo "DB Fehler, konnte die Datenbank nicht abfragen\n";
		    echo 'MySQL Error: ' . mysql_error();
		    exit;
		}

	while ($row = mysql_fetch_assoc($result_population)) {

		$this_population = $row['Value'] * 1000; 

		/*!! TEST IF CENTROIDS WORKS !!*/
		getCentroid("Germany");
		
	}


/*	
	REQUEST: PRICE
	------------------
*/
	$sql_price = "SELECT Country, Value FROM produktbiographien_annualPrices WHERE Country = '$set_country' && Year = '$set_year'";
	
	$result_price = mysql_query($sql_price, $link);

		if (!$result_price) {
		    echo "DB Fehler, konnte die Datenbank nicht abfragen\n";
		    echo 'MySQL Error: ' . mysql_error();
		    exit;
		}

	while ($row = mysql_fetch_assoc($result_price)) {

		$chicken_price = $row['Value']; 

	}

/*	
	REQUEST: TRADE-MATRIX
	------------------
*/
	$sql_matrix = "SELECT ReporterCountry, PartnerCountry, Element, Value FROM produktbiographien_chickenmatrix WHERE ReporterCountry = '$set_country'";
	
	$result_matrix = mysql_query($sql_matrix, $link);

		if (!$result_matrix) {
		    echo "DB Fehler, konnte die Datenbank nicht abfragen\n";
		    echo 'MySQL Error: ' . mysql_error();
		    exit;
		}
	
	$this_lat = $this_lon = 0; //must be set outside while-loop
		
	while ($row = mysql_fetch_assoc($result_matrix)) {

		$this_partner = $row['PartnerCountry']; 
		$this_value = adjust_value($row['Element'], $row['Value'], 2);
		$element = explode(" ", $row['Element']);
		$this_element = $element[0]; //"Import" or "Export"

		$partner_lat = $partner_lon = 0;
		
	/*
		## GET CENTROIDS ##
		## INSIDE MATRIX REQUEST ##
	*/
		$sql_centroids = "SELECT LAT, LON, SHORT_NAME, NAME_IN_FAO_DATA FROM produktbiographien_centroids WHERE SHORT_NAME = '$this_partner' OR NAME_IN_FAO_DATA = '$this_partner' OR SHORT_NAME = '$set_country'";
		$result_centroids = mysql_query($sql_centroids, $link);
		if (!$result_centroids) {
		    echo "DB Fehler, konnte die Datenbank nicht abfragen\n";
		    echo 'MySQL Error: ' . mysql_error();
		    exit;
		}
		while ($row2 = mysql_fetch_assoc($result_centroids)) {
			
			if($row2['SHORT_NAME'] == $set_country && $this_lat == 0 && $this_lon == 0){
				$this_lat = $row2['LAT'];
				$this_lon = $row2['LON'];
			}else if($row2['SHORT_NAME'] != $set_country){
				$partner_lat = $row2['LAT'];
				$partner_lon = $row2['LON'];
			}
		}

		$dist = distance($this_lat, $this_lon, $partner_lat, $partner_lon);
		$travel = $dist * $this_value;
		//echo " >>> distance = " . $dist . "km - value " . $this_value . "<br/>"	;


		
		$this_travel_data = array(
				"reporter" => $this_partner,
				"dist" => $dist,
				"value" => $this_value,
				"travel" => $travel
			);

			$all_travel_data[] = $this_travel_data;

		if($this_element=="Import"){
			$this_import_data = array(
				"reporter" => $this_partner,
				"dist" => $dist,
				"value" => $this_value,
				"travel" => $travel
			);
			$all_travel_import[] = $this_import_data;

		}else if($this_element=="Export"){
			$this_export_data = array(
				"reporter" => $this_partner,
				"dist" => $dist,
				"value" => $this_value,
				"travel" => $travel
			);
			$all_travel_export[] = $this_export_data;
		}
	}

/*
	REQUEST: CENTROIDS
	------------------
*/
/*
	$sql_centroids = "SELECT LAT, LON, SHORT_NAME, NAME_IN_FAO_DATA FROM produktbiographien_centroids WHERE SHORT_NAME = 'xx' OR NAME_IN_FAO_DATA = 'yy'";
	$result_centroids = mysql_query($sql_centroids, $link);
	if (!$result_centroids) {
	    echo "DB Fehler, konnte die Datenbank nicht abfragen\n";
	    echo 'MySQL Error: ' . mysql_error();
	    exit;
	}
*/

/*	
	REQUEST: PRODUCTION
	------------------
*/
	$sql_production = "SELECT Country, Value, Element, Item FROM produktbiographien_LivestockPrimeryProduction WHERE Country = '$set_country' AND Item = 'Meat, chicken' AND Year = '$set_year'";
	
	$result_production = mysql_query($sql_production, $link);

		if (!$result_production) {
		    echo "DB Fehler, konnte die Datenbank nicht abfragen\n";
		    echo 'MySQL Error: ' . mysql_error();
		    exit;
		}


	while ($row = mysql_fetch_assoc($result_production)) {

		$production_element = $row['Element']; 
		$production_item = $row['Item'];
		$production_value = $row['Value'];

		

		$per_year = adjust_value($production_element, $production_value, 2);

		$chickens_production = round($per_year / 365 / 24 / 60);

/*
		if($production_item == "Cattle"){
			$cattle_production = adjust_value($production_element, $production_value, 1);
		}	
		if($production_item == "Chickens"){
			$chickens_production = adjust_value($production_element, $production_value, 1);
		}	
		if($production_item == "Pigs"){
			$pigs_production = adjust_value($production_element, $production_value, 1);
		}
*/
	}


	function getCentroid($c){
		
		//checkRequestForError($result_centroids);

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


	/*
		CALCULATE PARTNER COUNTRIES
		= total_reporters
	*/
	$total_reporters = $total_distance = $total_value = 0;
	foreach ($all_travel_data as $key => $value) {
 		//echo "**".$value["reporter"] . "**: " . round($value["dist"]) . "km *(dist)*; " . round($value["value"]). " chickens *(trade)* <br/>";
		$total_reporters++;
		$total_distance += $value["dist"];
		$total_value += $value["value"];
	};
	$average_dist = $total_distance/$total_reporters;
	/*
		CALCULATE AVERAGE IMPORT DISTANCE
		= average_import
	*/
	$import_reporters = $import_distance = $total_import_travel = 0;
	foreach ($all_travel_import as $key => $value) {
		$import_reporters++;
		$import_distance += $value["dist"];
		$total_import_travel += $value["value"];
	};
	if($import_reporters==0){
		$average_import = 0;	
	}else{
		$average_import = $import_distance / $import_reporters;	
	}
	/*
		CALCULATE AVERAGE IMPORT DISTANCE
		= average_import
	*/
	$export_reporters = $export_distance = $total_export_travel = 0;
	foreach ($all_travel_export as $key => $value) {
		$export_reporters++;
		$export_distance += $value["dist"];
		$total_export_travel += $value["value"];
	};
	if($export_reporters==0){
		$average_export = 0;	
	}else{
		$average_export = $export_distance / $export_reporters;	
	}
	
	

	//echo "average_dist: " . round($average_dist) . ", total_value: " . $total_value;


	//echo "reporters: " . $total_reporters . "total_distance" . $total_distance;

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
	"average_dist" => $average_dist,
	"average_import" =>$average_import,
	"average_export" =>$average_export,
	"total_reporters" => $total_reporters,
	"price" => $chicken_price

		);

	echo json_encode($a);	

?>