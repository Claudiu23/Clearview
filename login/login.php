<?
/*
to add:
	token security
	ip check in the session
	maybe some other security
	(check)display errors on login form
	captcha check after several unsuccessful attempts
	(check)secure username and password against injection
*/

require_once('login_functions.php');
require_once('db_config.php');
session_start(); //must call session_start before using any $_SESSION variables

//validation variables
$username_not_empty=TRUE;
$username_valid=TRUE;
//$username_not_duplicate=TRUE;
$password_not_empty=TRUE;
$passwords_match=TRUE;
//$password_valid=TRUE;
//$captcha_valid= TRUE;

if (isLoggedIn()) 
	{
	//echo "Wellcome ".$_SESSION['username'];
	header('Location: memberzone.php');
	}
else
	{
	if (isset($_POST['username']) && isset($_POST['password']))
		{
		$username = $_POST['username'];
		$password = $_POST['password'];
	
		$conn = mysql_connect($dbhost, $dbuser, $dbpass);
		mysql_select_db($dbname, $conn);
		
		//secure data agains code injection
		$username = secure_data($username);
		$password = secure_data($password);
		
		//check if username is not empty
		if(empty($username)) {$username_not_empty = FALSE;}
		else {$username_not_empty = TRUE;}
		
		$query = 	"SELECT password, salt
					FROM ".$usertable."
					WHERE username = '$username';";
					
					
		$result = mysql_query($query);
		
		//check if username exists		
		if(mysql_num_rows($result) < 1) //no such user exists
			{
			$username_valid=FALSE;
			}
		else {$username_valid=TRUE;}
		
		//check if password is not empty
		if(empty($password)) {$password_not_empty = FALSE;}
		else {$password_not_empty = TRUE;}
		
		$userData = mysql_fetch_array($result, MYSQL_ASSOC);
		$hash = hash('sha256', $userData['salt'] . hash('sha256', $password) );
		
		//check if passwords match
		if($hash != $userData['password']) //incorrect password
		{
		$passwords_match=FALSE;
		} 
		else 
		{
		$passwords_match=TRUE;
		}
		if (($username_not_empty==TRUE)
			&& ($username_valid==TRUE)	
			&& ($password_not_empty==TRUE)
			&& ($passwords_match==TRUE))
			{
                        $_SESSION['username'] = $username;
			validateUser(); //sets the session data for this user
			header('Location: memberzone.php');
			}
		
		/*if($hash != $userData['password']) //incorrect password
			{
			header('Location: login.php');
			exit();
			}
		else
			{
			validateUser(); //sets the session data for this user
			}*/
			
		}
?>
	<!DOCTYPE HTML>
	<html>
	<head>
	<title>Login</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<style type="text/css">
	.invalid {
	border: 1px solid #000000;
	background: #FF00FF;
	}
	</style>
	</head>
	<body >
	<h2>User Login Form</h2>
	<br />
	<!-- Start of LOGIN form -->
	<form action="<? echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
	<table>
		<tr><td>Username:</td><td><input type="text" class="<? if (($username_not_empty==FALSE) || ($username_valid==FALSE) )  echo "invalid"; ?>" name="username" /></td></tr>
		<tr><td>Password:</td><td><input type="password" class="<? if (($password_not_empty==FALSE) || ($passwords_match==FALSE) ) echo "invalid"; ?>" name="password" /></td></tr>
	</table>
	<input type="hidden" name="token" value="<? echo $token;?>" />
	<input type="submit" name="login" value="Login" />
	<!-- Display validation errors -->
	<? //if ($captcha_valid==FALSE) echo '<font color="red">Please enter correct captcha</font><br />'; ?>
	<? if ($username_not_empty==FALSE) echo '<font color="red">You have entered an empty username.</font><br />'; ?>
	<? if ($username_valid==FALSE) echo '<font color="red">Username does not exist.</font><br />'; ?>
	<? //if ($username_not_duplicate==FALSE) echo '<font color="red">Please choose another username, your username is already used.</font><br />'; ?>
	<? if ($password_not_empty==FALSE) echo '<font color="red">Please enter a password.</font><br />'; ?>
	<? if (($passwords_match==FALSE)&&($username_valid==TRUE)) echo '<font color="red">Incorrect password.</font><br />'; ?>
	<? //if ($password_valid==FALSE) echo '<font color="red">Your password should be alphanumeric and greater 7 characters and less than 20 characters.</font><br />'; ?>
	<? //if ($captcha_valid==FALSE) echo '<font color="red">Your captcha is invalid.</font><br />'; ?>
	
	</form>
	Not Registered yet? <a href = "register.php">Create and account.</a>
<?		
	}
?>