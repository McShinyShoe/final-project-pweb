<?php
function dbconn() {
	// koneksi database
	$db_server="console.shinyshoe.net";
	$db_username="shinychat";
	$db_password="admin123";
	$db_database="ShinyChat";
	$conn=mysqli_connect($db_server,$db_username,$db_password, $db_database);
	if (!$conn) {
		die("koneksi error");
	}
	return $conn;
}
?>