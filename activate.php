<?php
// Change the below variables to reflect your MySQL database details
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';
// Try and connect using the info above
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
// Check for connection errors
if (mysqli_connect_errno()) {
	// If there is an error with the connection, stop the script and display the error
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// First we check if the email and code GET parameters exists
if (isset($_GET['email'], $_GET['code']) && !empty($_GET['code'])) {
	// Parameters are set, so we can proceed with the activation
	if ($stmt = $con->prepare('SELECT * FROM accounts WHERE email = ? AND activation_code = ?')) {
		$stmt->bind_param('ss', $_GET['email'], $_GET['code']);
		$stmt->execute();
		// Store the result so we can check if the account exists in the database.
		$stmt->store_result();
		// Check if account exists with the emai and code
		if ($stmt->num_rows > 0) {
			// Account exists with the requested email and code, update the activation code to 'activated'
			if ($stmt = $con->prepare('UPDATE accounts SET activation_code = "activated" WHERE email = ? AND activation_code = ?')) {
				// bind parameters (s = string, i = int, b = blob, etc)
				$stmt->bind_param('ss', $_GET['email'], $_GET['code']);
				$stmt->execute();
				// Output success message
				echo 'Your account is now activated! You can now login!<br><a href="index.php">Login</a>';
			}
		} else {
			echo 'The account is already activated or doesn\'t exist!';
		}
	}
}
?>