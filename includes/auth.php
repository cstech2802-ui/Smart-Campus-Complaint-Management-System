<?php
if(session_status()===PHP_SESSION_NONE) session_start();
function require_login(){if(!isset($_SESSION["user_id"])){header("Location: ../login.php");exit;}}
function require_role($role){require_login();if($_SESSION["role"]!==$role){header("Location: ../index.php");exit;}}
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,"UTF-8");}
?>