<?php
    $userinfo = getUserInfo($_SESSION["userid"]);

    if (isset($_POST["change_private_info"])) {
        $mail = htmlentities($_POST["email"]);
        $username = htmlentities($_POST["username"]);
        
        if (($mail != $userinfo['mail']) or ($username != $_SESSION["username"]) or (!empty($_POST["password"]) and !empty($_POST["password2"]))) {
            //Ha habido algún cambio
            if (!empty($_POST["password"]) and !empty($_POST["password2"])) {
                if ($_POST["password"] == $_POST["password2"]) {
                    $newPassword = md5(htmlentities($_POST["password"]));
                    $sql1 = "UPDATE `users` SET password = '".$newPassword."' WHERE id = '".$_SESSION["userid"]."'";
                } else {
                    $error = '004x001';
                }
            }

            if($mail != $userinfo['mail']) {
                $sql2 = "UPDATE `users` SET mail = '".$mail."' WHERE id = '".$_SESSION["userid"]."'";
            }

            if ($username != $userinfo["username"]) {
                $sqlquery = "SELECT count(id) FROM `users` WHERE username = '".$username."'";
                $goquery = $db->query($sqlquery);
                $goresp = $goquery->fetch_array();

                if ($goresp[0] > 0) {
                    $error = '004x002';
                } else {
                    $sql3 = "UPDATE `users` SET username = '".$username."', can_change_username = '0' WHERE id = '".$_SESSION["userid"]."'";
                }
            }

            if (isset($sql1)) {
                $db->query($sql1) or die($db->error);
            } elseif (isset($sql2)) {
                $db->query($sql2) or die($db->error);;
            } elseif (isset($sql3)) {
                $db->query($sql3) or die($db->error);;
                echo '<script type="text/JavaScript"> location.assign("logout.php"); </script>';
            }

            if (!isset($error)) {
                $alert = "Se han cambiado tus datos correctamente.";
            }

        }
    }
?>
<div align="center" valign="top">
        <?php
            if (isset($error)) {
                echo "<p><div class='error'>".showChatError($error)."</div></p>";
            }

            if (isset($alert)) {
                echo "<p><div class='alert'>".$alert."</div></p>";
            }
        ?>
    <h1>Cambiar información personal</h1><br>

    <table>
    <form name="change_personal_info" method="post" action="">
        <tr>
        <td>Nombre de usuario</td>
        <?php
            if ($userinfo['can_change_username'] == 0) {
                echo '<td><input type="text" name="username" value="'.$_SESSION["username"].'" disabled />';
                echo '<input type="hidden" name="username" value="'.$_SESSION["username"].'" />';
            } else {
                echo '<td><input type="text" name="username" value="'.$_SESSION["username"].'" />';
            }
        ?>
        <tr>
        <td>Email</td>
        <td><input type="email" name="email"  value="<?=$userinfo["mail"];?>" />
        </tr>
        <tr>
        <td>Contraseña</td>
        <td><input type="password" name="password"  value="" /></td>
        </tr>
        <tr>
        <td>Repite la Contraseña</td>
        <td><input type="password" name="password2"  value="" /></td>
        </tr>  
    </table>
    <p><input type="submit" name="change_private_info" value="Cambiar información personal" />
    <br><br>
    </form> 
    <?php
    if ($userinfo['can_change_username'] == 0) {
        echo '<p>Para cambiar el nombre de usuario debes pedirle a un Guardia que te habilite la opción.</p>';
    } else {
        echo '<p>Al cambiar de nombre de usuario, te desconectarás del chat y tendrás que volver a entrar, esta vez usando el nuevo nombre de usuario.</p>';
    }
    ?> 
    <p>Si no quieres cambiar la contraseña deja ambos campos vacíos.
    
</div>