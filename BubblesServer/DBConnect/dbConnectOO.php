<?php
	$conn = new mysqli("localhost", "projectwolf", "projectwolf15", "PROJECTWOLF");

	if (mysqli_connect_errno()) {
		die("Connect failed: " . mysqli_connect_error());
	}
?>
