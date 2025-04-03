<?php

//Constant definitions of database variables


define("DB_HOST","localhost");
define("DB_USER","root");
define("DB_PASS","MahitNahi@12");
define("DB_DATABASE","lex_social");

$connect = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_DATABASE);

// Check connection
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
  }
  
