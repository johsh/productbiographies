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
	$showCattle = $_GET['showCattle'];
	if($showCattle == true){
		$set_cattle = "Cattle";
	}
	$showChickens = $_GET['showChickens'];
	$showPigs = $_GET['showPigs'];

	function setAnimal(){

	}

/*
	## REQUEST CLAUSES ##
*/
	//$priority_clause = "(priority1=" . $set_priority1 . " OR priority6=" . $set_priority1.")";
	//$animal_clause = "(Item=" . $set_cattle . " OR Item=" . $set_chickens. " OR Item=" . $set_pigs.")";
	$animal_clause = "(Item = 'Cattle' OR Item = 'Chickens' OR Item = 'Pigs')";


/*	
	####################
	## BEGIN REQUESTS ##
	####################
*/

/*	
	REQUEST: TRADE
	------------------
*/
	$sql_trade = "SELECT Country, Value, Item, Element FROM trade WHERE Country = '$set_country' && Year = '$set_year' && $animal_clause";//Item = 'Chickens'";


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
		$this_value = filter_element($this_element, $this_value, "import");
	}




/*	
	REQUEST: POPULATION
	------------------
*/
	$sql_population = "SELECT Country, Value FROM population WHERE Country = '$set_country' && Year = '$set_year'";
	
	$result_population = mysql_query($sql_population, $link);

		if (!$result_population) {
		    echo "DB Fehler, konnte die Datenbank nicht abfragen\n";
		    echo 'MySQL Error: ' . mysql_error();
		    exit;
		}

	while ($row = mysql_fetch_assoc($result_population)) {

		$this_population = $row['Value'] * 1000; 
		
	}






	function filter_element($element_to_filter, $element_value, $requested_trade_mode){
		/*
		Checks for the requested trade-mode (import/export) and returns the absolute/refined value
		*/
		$element_trade_mode = strtolower($element_to_filter); //export quantity (1000 head)
		$element_array = explode(" ", $element_trade_mode);
		$element_trade_mode = $element_array[0];

		$requested_trade_mode = strtolower($requested_trade_mode); //export
		

		// compare request and element trade-mode
		if($element_trade_mode == $requested_trade_mode){
			$unit = $element_array[2];
			if($unit == "(1000"){
				// Value times 1000 when unit is (1000 head)	
				$element_value = $element_value * 1000;
				return $element_value;
			}
		}else{
			return "0";
		}
	}


	$a = array(
	"val" => $this_value,
	"country" => $set_country,
	"population" => $this_population,
	"cattle" => $showCattle,
	"chickens" => $showChickens,
	"pigs" => $showPigs
		);

	echo json_encode($a);	

?>