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

/*
	## REQUEST ##
*/
	$sql = "SELECT Country, Value, Item, Element FROM trade WHERE Country = '$set_country' && Year = 2011 && Item = 'Chickens'";


	$result = mysql_query($sql, $link);

	if (!$result) {
	    echo "DB Fehler, konnte die Datenbank nicht abfragen\n";
	    echo 'MySQL Error: ' . mysql_error();
	    exit;
	}

	while ($row = mysql_fetch_assoc($result)) {
/*
		## PROCESSING REQUESTED VALUES ##
*/
		$this_element = $row['Element']; 
		$this_item = $row['Item']; 
		
		$this_value = $row['Value']; 
		$this_value = filter_element($this_element, $this_value, "import");
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
	"country" => $set_country
		);

	echo json_encode($a);	

?>