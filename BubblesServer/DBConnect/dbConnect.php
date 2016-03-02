<?php
	$conn = mysqli_connect("localhost", "BubblesMaster", "projectwolf15", "BUBBLES");

	if (!$conn) {
		die('Could not connect to MySQL: ' . mysqli_error());
	}
?>
