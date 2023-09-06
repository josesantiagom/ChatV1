<?php
session_start();
session_cache_limiter("no cache");
include("config.php");
mostrarErrores();

if (!isset($_SESSION["username"])) {
    echo '<script type="text/JavaScript"> location.assign("login.php"); </script>';
} else {
    updateLastOnline($_SESSION["username"], time());
    updateLastOnline("_bots", time());
}

$roomInDb = getUserInfo($_SESSION["userid"])['current_room'];
if ($roomInDb != $_SESSION["chatroom"]) {
    if ($roomInDb == "lobby") {
        $_SESSION["chatroom"] = $roomInDb;
        echo '<script type="text/JavaScript"> location.assign("lobby.php"); </script>';
    } else {
        $_SESSION["chatroom"] = $roomInDb;
        echo '<script type="text/JavaScript"> location.assign("chat.php"); </script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=getConfig('str', 'name');?> - <?=getConfig('str', 'subtitle');?></title>
    <script src="http://code.jquery.com/jquery-latest.js"></script>
</head>
<body>
<table>
        <?php
            if (isInGuard($_SESSION["username"])) {
                echo '<tr class="myname"><td>🎖️'.$_SESSION["username"].'</td></tr>';
            } else {
                echo '<tr class="myname"><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$_SESSION["username"].'</td></tr>';
            }

            $time = time()-2;

            $sql = "SELECT username FROM `users` WHERE current_room = '".$_SESSION['chatroom']."' AND username != '".$_SESSION["username"]."'  AND last_online >= '".$time."' ORDER BY username ASC";
            $query = $db->query($sql);

            $i = 1;

            for ($i = 0; $resp = $query->fetch_array(); $i++) {
                if ($i%2 == 0) {
                    if (isInGuard($resp['username'])) {
                        echo '<tr class="userlist_par"><td>🎖️'.$resp['username'].'</td></tr>';
                    } elseif (isBot($resp['username'])) {
                        echo '<tr class="userlist_par"><td>🎲'.$resp['username'].'</td></tr>';
                    } else {
                        echo '<tr class="userlist_par"><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$resp['username'].'</td></tr>';
                    }
                } else {
                    if (isInGuard($resp['username'])) {
                        echo '<tr class="userlist_impar"><td>🎖️'.$resp['username'].'</td></tr>';
                    } elseif (isBot($resp['username'])) {
                        echo '<tr class="userlist_impar"><td>🎲'.$resp['username'].'</td></tr>';
                    } else {
                        echo '<tr class="userlist_impar"><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$resp['username'].'</td></tr>';
                    }
                }
            }
        ?>
    </table>
</body>
</html>