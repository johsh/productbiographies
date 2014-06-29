<?php
//LOCAL
/*
if (!$link = mysql_connect('localhost', 'root', 'root')) {
	    echo 'Keine Verbindung zu mysql';
	    exit;
	}

	if (!mysql_select_db('produktbiographien', $link)) {
	    echo 'Konnte Schema nicht selektieren';
	    exit;
	}
*/

//ONLINE
if (!$link = mysql_connect("martinvonlupin.de.mysql", "martinvonlupin_", "Jvaem4V8")) {
	    echo 'Keine Verbindung zu mysql';
	    exit;
	}

	if (!mysql_select_db("martinvonlupin_", $link)) {
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
	= 0;

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
		
	}

/*	
	REQUEST: PRODUCTION
	------------------
*/
	$sql_production = "SELECT Country, Value, Element, Item FROM produktbiographien_production WHERE Country = '$set_country' && Year = '$set_year'";
	
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

		if($production_item == "Cattle"){
			$cattle_production = adjust_value($production_element, $production_value, 1);
		}	
		if($production_item == "Chickens"){
			$chickens_production = adjust_value($production_element, $production_value, 1);
		}	
		if($production_item == "Pigs"){
			$pigs_production = adjust_value($production_element, $production_value, 1);
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
	"pigs_production" => $pigs_production
		);

	echo json_encode($a);	

?>