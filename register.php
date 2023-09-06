<?php
session_start();
session_cache_limiter("no cache");
include("config.php");
mostrarErrores();

if (isset($_SESSION["username"])) {
    header("Location: index.php");
}

if (isset($_POST["register"])) {
    $username = htmlentities($_POST["username"]);
    $password = md5(htmlentities($_POST["password"]));
    $mail = htmlentities($_POST["mail"]);

    $letras = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    $num = "0123456789";
    $symbols = "_.-";

    //Comprobamos el formato del email
    if (strstr($mail, "@") and strstr($mail, ".")) {
        $first_separate = explode("@",$mail);
        if (strstr($first_separate[1],".")) {
            if (substr($first_separate[1],0,1) == ".") {
                $error = '001x005';
                //echo "El primer carácter después del @ es un .".substr($first_separate[1],0,1);
            } else {
                $second_separate = explode(".",$first_separate[1]);
                if (!isset($second_separate[1]) or $second_separate[1] == '') {
                    $error = '001x005';
                    //echo "No hay nada después del punto ".$second_separate[1];
                }
            }
        } else {
            $error = '001x005';
            //echo "No hay punto en la segunda parte del email".$first_separate[1];
        }
    } else {
        $error = '001x005';
        //echo = "El email no tiene @ o . ".$mail;
    }

    //Comprobamos que el username no tiene carácteres prohibidos
    for($i=0;$i<strlen($username);$i++){ 
        if (!strstr($letras,substr($username,$i,1)) and !strstr($num,substr($username,$i,1)) and !strstr($symbols,substr($username,$i,1))) {
            $error = '001x003';
        }
    } 
    
    //Comprobamos que tiene al menos 3 carácteres
    if (strlen($username) < 3) {
        $error = '001x004';
    }
    
    if (!isset($error)) {
        //Comprobamos que el nick o el email no existan
        $sql = "SELECT id FROM `users` WHERE username = '".$username."'";
        $sql2 = "SELECT id FROM `users` WHERE mail = '".$mail."'";
        $query = $db->query($sql) or die($db->error);
        $query2 = $db->query($sql2) or die($db->error);

        if ($query->num_rows > 0) {
            $error = "001x001";
        } elseif ($query2->num_rows > 0) {
            $error = "001x002";
        } else {
            $sql3 = "INSERT INTO `users` (username, mail, password, role) VALUES ('".$username."', '".$mail."', '".$password."', '0')";
            $query3 = $db->query($sql3);
            $login = true;
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
    <title><?=getConfig('str', 'name');?> - <?=getConfig('str', 'subtitle');?></title>
</head>
<body>
    <header>
        <div class="return_squad">
            <a href="login.php" class="a2">⏪ VOLVER</a>
        </div>
    </header>
    <article>
        <section>
        <?php
            if (isset($error)) {
                echo "<div class='error'>".showChatError($error)."</div>";
            }

            if (isset($login)) {
                echo "<div class='alert'>Registro correcto ¡Ahora puedes entrar al chat!</div>";
            }
        ?>
        <form method="post" action="">
            <div class="login_title">Registro_</div>
            <div class="cuadrado">
                <div class="login_form">
                    <p>Nombre de usuario<br>
                    <input type="text" name="username" /></p>
                    <p>Contraseña<br>
                    <input type="password" name="password" /></p>
                    <p>Email<br>
                    <input type="text" name="mail" /></p>
                    <p></p>
                    <p><br><button value="true" name="register">Entrar</button><br></p>
                </div>
            </div>
        </form>
        </section>
    </article>
    <footer></footer>
</body>
</html>