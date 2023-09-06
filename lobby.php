<?php
session_start();
session_cache_limiter("no cache");
include("config.php");
mostrarErrores();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
} else {
    updateLastOnline($_SESSION["username"], time());
}

if (getUserInfo($_SESSION["userid"])['can_move'] == 0) {
    $_SESSION["chatroom"] = getUserInfo($_SESSION["userid"])["current_room"];
    header("Location: lobby.php?goroom=".$_SESSION["chatroom"]);
}

$_SESSION["chatroom"] = "lobby";
$sql = $db->query("UPDATE `users` SET current_room = 'lobby' WHERE id = '".$_SESSION["userid"]."'");

if (isset($_GET["goroom"])) {
    $room = htmlentities($_GET["goroom"]);
    $sql = "SELECT * FROM `rooms` WHERE shortname = '".$room."'";
    $query = $db->query($sql);
    $resp = $query->fetch_array();

    if ($query->num_rows > 0) {
        //La sala existe, comprobamos todo lo demás
        $conditions = $resp["conditions"];

        if (AllowedInRoom($_SESSION['userid'], $room) == 'ok') {
            $_SESSION["chatroom"] = $room;
            $sql = $db->query("UPDATE `users` SET current_room = '".$room."' WHERE id = '".$_SESSION["userid"]."'");
            header("Location: chat.php");
        } else {
            $error = AllowedInRoom($_SESSION["userid"], $room);
        }
    } else {
        header("Location: lobby.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/style.css" rel="stylesheet">
    <title><?=getConfig('str', 'name');?> - <?=getConfig('str', 'subtitle');?></title>
</head>
<body>
<header class="return_squad"><div align="right" valign="middle">¡Hola <?=$_SESSION["username"];?>! <a href=logout.php class="a2"> ❌ </a></div><br></header>
    <article>
        <section>
        <?php
            if (isset($error)) {
                echo "<div class='error'>".showChatError($error)."</div>";
            }
        ?>
        <div class="login_title">Salas de chat_</div>
            <div class="cuadrado">
            <div class="login_form">
            <?php
                $sql = "SELECT * FROM `rooms` ORDER by id ASC";
                $query = $db->query($sql);
                $time = time()-3;

                while ($resp = $query->fetch_array()) {
                    $sql2 = "SELECT count(id) FROM `users` WHERE current_room = '".$resp["shortname"]."' and last_online > '".$time."' and bot = '0'";
                    $query2 = $db->query($sql2);
                    $resp2 = $query2->fetch_array();

                    echo '<p><a href="?goroom='.$resp["shortname"].'" class="a3">'.$resp["name"].' ('.$resp2[0].')</a></p>';
                }
            ?>
            </div>
            </div>
        </section>
    </article>
</body>
</html>