<?php $servername = "localhost";
$username = "root";
$password = "password";
$dbname = "root";
$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error){
	echo "<p class='alert alert-danger text-center'><strong>Some internal error occured</strong></p>".$conn->connect_error;
	die();
}
?>
