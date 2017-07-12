<?php
	$conn = mysqli_connect("localhost", "boppo", "march2015", "BOPPO");

	if (!$conn) {
		die('Could not connect to MySQL: ' . mysqli_error());
	}
?>
