<?php
session_start();
session_cache_limiter("no cache");
include("config.php");
mostrarErrores();

if (isset($_SESSION["username"])) {
    header("Location: index.php");
}

if (isset($_POST["login"])) {
    $username = htmlentities($_POST["username"]);
    $password = md5(htmlentities($_POST["password"]));

    $sql = "SELECT * FROM `users` WHERE username = '$username'";
    $query = $db->query($sql);

    if ($query->num_rows > 0) { //El usuario existe
        $userinfo = $query->fetch_array();
        if ($password === $userinfo['password']) { //La contraseña es correctisima
            //Actualizamos el last_online
            updateLastOnline($username, time());

            //Creamos la variable de sesión
            $_SESSION["username"] = $username;
            $_SESSION["userid"] = $userinfo['id'];
            $_SESSION["role"] = $userinfo['role'];
            $_SESSION["chatroom"] = 'lobby';
            header("Location: lobby.php");
        } else {
            $error = "002x002";
        }
    } else {
        $error = "002x001";
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
    <header></header>
    <article>
        <section>
        <?php
            if (isset($error)) {
                echo "<div class='error'>".showChatError($error)."</div>";
            }
        ?>
        <form method="post" action="">
            <div class="login_title">Login_</div>
            <div class="cuadrado">
                <div class="login_form">
                    <p>Nombre de usuario<br>
                    <input type="text" name="username" /></p>
                    <p>Contraseña<br>
                    <input type="password" name="password" /></p>
                    <p></p>
                    <p><br><button value="true" name="login">Entrar</button><br></p>
                    <p><a href="register.php">Registrarse</a></p>
                    <p><a href="">Olvidé mi contraseña</a></p>
                </div>
            </div>
        </form>
        </section>
    </article>
    <footer></footer>
</body>
</html>