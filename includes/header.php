<?php if(session_status()===PHP_SESSION_NONE)session_start(); ?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Smart Campus</title><link rel="stylesheet" href="../assets/css/style.css"></head><body>
<header><b>▣ Smart Campus</b><nav><?php if(isset($_SESSION["user_id"])): ?>Hi, <?=e($_SESSION["name"])?> · <a href="../logout.php">Logout</a><?php else: ?><a href="../login.php">Login</a> · <a href="../register.php">Register</a><?php endif;?></nav></header><main>