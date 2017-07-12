<?php
	$conn = new mysqli("localhost", "boppo", "march2015", "BOPPO");

	if (mysqli_connect_errno()) {
		die("Connect failed: " . mysqli_connect_error());
	}
?>
