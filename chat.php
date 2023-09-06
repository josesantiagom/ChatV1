<?php
session_start();
session_cache_limiter("no cache");
include("config.php");
include("comandos.php");
mostrarErrores();


if (!isset($_SESSION["username"])) {
    header("Location: login.php");
} else {
    updateLastOnline($_SESSION["username"], time());
    updateLastOnline("_bots", time());

    if (!isset($_GET["view"]) and !isset($_GET["return"])) {
        chatRefresh();
    }
}

if (isset($_POST["profile"])) {
    header("Location: chat.php?view=profile");
}

if (isset($_POST["lobby"])) {
    header("Location: lobby.php");
}

if (isset($_POST["logout"]))  {
    header("Location: logout.php");
}

if (isset($_POST["sendmsg"])) {
    $msg = htmlentities($_POST["msg"]);

    if (isCommand($_POST["msg"])) {
        executeCommand($msg);
    } else {
        if (!empty($msg) or $msg != "") {
            $uid = $_SESSION["userid"];
            $rid = getRoomInfo($_SESSION["chatroom"])["id"];
            $conditions = 'normal';
            $date = time();
            $destiny = 'public';
        
            if (isInGuard($_SESSION["username"])) {
                $conditions .= ';guard_on';
            }

            $insertinto = $db->query("INSERT INTO `chat` (uid, rid, msg, conditions, date, destiny)
                                VALUES('".$uid."', '".$rid."', '".$msg."', '".$conditions."', '".$date."', '".$destiny."')");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/style.css" rel="stylesheet">
    <?php
        if (file_exists('css/room_styles/'.$_SESSION["chatroom"].'.css'))  {
            echo '<link href="css/room_styles/'.$_SESSION["chatroom"].'.css" rel="stylesheet">';
        } 
    ?>
    <title><?=getConfig('str', 'name');?> - <?=getConfig('str', 'subtitle');?></title>
    <script src="http://code.jquery.com/jquery-latest.js"></script>
</head>
<body>
    <header>
        <div class="return_squad">
            <div class="a2">
                <a href="chat.php?view=pm">🪧 Mensajes privados (0)</a>
                <?php
                    if (isGuard($_SESSION["userid"])) {
                ?> 
                | <a href="?view=guard_panel">🎖️ Panel de guardia
                <?php 
                }
                ?> 
                &nbsp;&nbsp;&nbsp;&nbsp;</a>
            </div>
        </div>
    </header>
    <div class="userlist_title">Usuarios_</div>
    <div class="chatname_title"><?=getRoomInfo($_SESSION["chatroom"])['name'];?>_</div>
    <aside>
        <script>
            $(document).ready(function() {
                var refreshId =  setInterval( function(){
                    $('#left_column').load('usrlist.php'); //Se actualiza el div
                }, 1000 );
            });
        </script>
        <div class="left_column" id="left_column">
        <table>
            <?php

             if (isInGuard($_SESSION["username"])) {
                echo '<tr class="myname"><td>🎖️'.$_SESSION["username"].'</td></tr>';
            } else {
                echo '<tr class="myname"><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$_SESSION["username"].'</td></tr>';
            }
            $time = time()-2;

            $sql = "SELECT username FROM `users` WHERE current_room = '".$_SESSION['chatroom']."' AND username != '".$_SESSION["username"]."' AND last_online >= '".$time."'  ORDER BY username ASC"; //Añadir el tiempo
            $query = $db->query($sql);

            $i = 1;

            while ($resp = $query->fetch_array()) {
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

            $i++;
            }
        ?>
        </table>
        </div>
    </aside>
    <section>
        <div class="menu_case">
            <form name="menu" method="post" action="">
                <p>
                <input type="submit" name="options" value="Crear sala privada" disabled class="menu_button" />
                </p><p>
                <input type="submit" name="profile" value="Mi perfil" class="menu_button" />
                <?php
                    if (getUserInfo($_SESSION["userid"])['can_move'] == 1) {
                        echo '<p><input type="submit" name="lobby" value="Ir al Lobby" class="menu_button" /></p>';
                    } else {
                        echo '<p><input type="submit" name="lobby" value="Ir al Lobby" class="menu_button" disabled /></p>';
                    }
                ?>
                <p>
                <input type="submit" name="logout" value="Desconectarse" class="menu_button" />
                </p>
            </form>
        </div>
    </section>
    
    <article>
    <?php
        if (!isset($_GET["view"])) {
    ?>
    <script>
            $(document).ready(function() {
                var refreshId =  setInterval( function(){
                    $('#chat_box').load('chattag.php'); //Se actualiza el div
                }, 1000 );
            });

        var element = document.getElementById("chat_box");
        element.scrollTop = element.scrollHeight;
    </script>
        <div class="chat_box" id="chat_box">
            <table>
            <?php
             $time = time()-600;
             $sql = "SELECT * FROM `chat` WHERE rid = '".getRoomInfo($_SESSION['chatroom'])['id']."' and date > '".getUserInfo($_SESSION["userid"])['last_chat_refresh']."' ORDER BY ID ASC"; //Añadir and date >= '".$time."'
             $query = $db->query($sql) or die($db->error);

             while ($resp = $query->fetch_array()) {
                if ($resp['conditions'] == 'normal') {
                    echo '<tr class="msg"><td>'.date("H:i", $resp['date'])."<font color=#".getUserInfo($resp["uid"])['color']."><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#60;".getUserInfo($resp["uid"])['emoji']."".getUserInfo($resp["uid"])['username']."&#62;</b></font>: ".$resp["msg"].'</td></tr>';
                } elseif (str_contains($resp['conditions'], 'public_bot')) {
                    echo '<tr class="msg"><td>'.date("H:i", $resp['date'])."<font color=#".getUserInfo($resp["uid"])['color']."><b>🎲&#60;".getUserInfo($resp["uid"])['emoji']."".getUserInfo($resp["uid"])['username']."&#62;</b></font>: ".$resp["msg"].'</td></tr>';
                } elseif (str_contains($resp['conditions'], 'private_bot') and $resp['destiny'] == $_SESSION["username"]) {
                    echo '<tr class="msg"><td><img src="/img/'.getUserInfo($resp["uid"])['username'].'.png" width="75" height="75" style="vertical-align: bottom" /><b><font color="#ff931c">'.getUserInfo($resp["uid"])['username'].'</font> te dice:</b>'.$resp["msg"].'</td></tr>';
                } elseif (str_contains($resp['conditions'], 'guard_on')) {
                    echo '<tr class="msg"><td>'.date("H:i", $resp['date'])."<font color=#".getUserInfo($resp["uid"])['color']."><b>🎖️&#60;".getUserInfo($resp["uid"])['emoji']."".getUserInfo($resp["uid"])['username']."&#62;</b></font>: ".$resp["msg"].'</td></tr>';
                }
            } 
            ?>
            </table>
        </div>
    <?php
        } else {
            $view = htmlentities($_GET["view"]);
            echo '<div class="panel_box" id="panel_box">';

            if ($view == 'profile' or $view == 'pm') {
                include($view.".php");
            } elseif ($view == 'guard_panel' and isGuard($_SESSION["userid"])) {
                include($view.".php");
            } else {
                echo "<div class='imgcenter'><p>¡Ha ocurrido un error cargando la página! <a href='chat.php'>⬅️ Volver</a></p><p><img src='img/modulerror.png' width='30% height='30%' /></p></div>";
            }

            echo '</div>';
        }
    ?>
    </article>
    <section>
            <div class="chat_case">
            <form name="sendmsg" method="post" action="?return=">
                <br>
                <input type="text" name="msg" placeholder="¡Escribe tu mensaje!" autofocus size="95" class="inputmsg" />
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="submit" name="sendmsg" value="Enviar" class="sendmsg" />
                &nbsp;&nbsp;&nbsp;&nbsp;
            </form>
            </div>
    </section>            
</body>
</html>