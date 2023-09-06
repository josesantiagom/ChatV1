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
    <script>
        var element = document.getElementById("chat_box");
        element.scrollTop = element.scrollHeight;
    </script>
    <div id="chat_box">
    <table>
            <?php
            $time = time()-600;
             $sql = "SELECT * FROM `chat` WHERE rid = '".getRoomInfo($_SESSION['chatroom'])['id']."'  and date > '".getUserInfo($_SESSION["userid"])['last_chat_refresh']."'  ORDER BY ID ASC"; //Añadir and date >= '".$time."'
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
    </body>
</html>