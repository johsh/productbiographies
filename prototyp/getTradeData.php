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

	//GET INPUT FROM INDEX.PHP
	$set_country = addslashes($_GET['country']);

	$sql = "SELECT Country, Value, Item, Element FROM trade WHERE Country = '$set_country' && Year = 2011 && Item = 'Cattle'";


	$result = mysql_query($sql, $link);

	if (!$result) {
	    echo "DB Fehler, konnte die Datenbank nicht abfragen\n";
	    echo 'MySQL Error: ' . mysql_error();
	    exit;
	}

	while ($row = mysql_fetch_assoc($result)) {
		$this_item = $row['Element']; 
		$this_item = $row['Item']; 
		$this_value = $row['Value']; 
		//echo $this_value . "<br/>";
	}

	
	$a = array(
	"val" => $this_value,
	"country" => $set_country
		);

	echo json_encode($a);	

?>