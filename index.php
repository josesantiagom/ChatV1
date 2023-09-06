<?php
session_start();
session_cache_limiter("no cache");
include("config.php");
mostrarErrores();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
} else {
    //header("Location: ");
    header("Location: lobby.php");
}
?>