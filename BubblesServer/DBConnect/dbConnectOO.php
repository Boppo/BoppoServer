<?php
	$conn = new mysqli("localhost", "BubblesMaster", "projectwolf15", "BUBBLES");

	if (mysqli_connect_errno()) {
		die("Connect failed: " . mysqli_connect_error());
	}
?>
