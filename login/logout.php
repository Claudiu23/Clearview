<?
include('login_functions.php');
session_start();
logout();
header('Location: login.php');

?>