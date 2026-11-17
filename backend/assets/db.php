<?php
$hostname_forumtest = "localhost";
$database_forumtest = "gunit";
$username_forumtest = "gunit";
$password_forumtest = "gunit";


try {
  $forum = new PDO("mysql:host=$hostname_forumtest;dbname=$database_forumtest", $username_forumtest, $password_forumtest);
  // set the PDO error mode to exception
  $forum->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  throw $e;
}