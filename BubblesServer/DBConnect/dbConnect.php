<?php
	$conn = mysqli_connect("localhost", "bubbles", "march2015", "BUBBLES");

	if (!$conn) {
		die('Could not connect to MySQL: ' . mysqli_error());
	}
?>
