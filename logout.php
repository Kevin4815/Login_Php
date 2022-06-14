<?php

    session_start(); // Initialize the session
    session_unset(); // Disable the session
    session_destroy(); // Destroy the session
    setcookie('auth', '', time()-1, '/', null, false, true); // Destroy the cookie

    //Redirect to the home page if the user is disconnected
    header('location: index.php');
    exit();

?>