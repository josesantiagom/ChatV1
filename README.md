# ChatV1

CHAT V1 - ESTILO LYCOS CHAT, COMPLETAMENTE EN PHP + MYSQL + JS
Proyecto para el PORTAFOLIO

Autor: Jose Santiago Muñoz
Inicio del proyecto: agosto 2023, final del proyecto: septiembre 2023

##Índice
- [Introducción] (#introducción)
- [Sistema de usuarios] (#sistema-de-usuarios)

## Introducción
ChatV1 es un proyecto que nace de dos necesidades: la primera es la necesidad de añadir proyectos en el portafolio que puedan demostrar mis capacidades como programador PHP, y, la segunda, la necesidad de que este proyecto consistiera en sí un reto mental.

Esta idea está inspirada en el [Lycos/Yahoo Chat (En otros paises se llamaba WorldBiggestChat)] (https://es.lycoschat.com/) que tuvo una gran popularidad entre el 2003 y el 2013. En resumidas cuentas, se trataba de un webchat programado en ASP (Active Server Pages) y basado en IRC (Internet Relay Chat) que tenía la particularidad de simular un barco (aunque de forma textual). En la actualidad ese chat sigue funcionando, y, aunque está muy cambiado, mantiene sus puntos más esenciales.

El reto mental de este proyecto, que indica las condiciones en las que ha sido desarrollado son las siguientes:
1. Solo se podrán usar los lenguajes HTML5, CSS3, PHP, y MySQL. Se permite JavaScript únicamente en lo estrictamente necesario.
1. No se usará servidor de chat. Toda la arquitectura debe realizarse mediante querys MySQL, usando MySQLi.
1. Todo lo que se pueda hacer en el chat debe hacerse en la misma ventana (para simplificar el proyecto, ya que hay otro proyecto para el portafolio que consiste en un CMS)
1. No se pueden usar iframes, la misma página de chat debe controlar el flujo.
1. Debe contener ciertos elementos de optimización automática de base de datos.

La idea tras estas normas era que fuera un proceso verdaderamente desafiante del que pudiera aprender ciertos apectos del PHP más puro.

## Sistema de Usuarios

Existe tanto un [login] (https://chatv1.josesantiago.es/login.php) como un [registro de usuarios] (https://chatv1.josesantiago.es/register.php).

Aunque de algunos de los campos de la base de datos hablaremos más adelante (*last_chat_refresh, current_room, color, o emoji* cuando hablemos del chat, *can_move* cuando hablemos de rangos y *can_change_username* cuando hablemos del perfil). Vamos a explicar algunos de los aspectos más importantes de este sistema de usuarios:

- *id* es la clave primaria de identificación de cada usuario.
- *username, mail* y *password* corresponden a los datos de acceso e identificación.
- *role* y *guard* son campos que indican el rango de la persona y si está o no de guardia. Ver [lista de conectados] (#lista-de-conectados) y [rangos] (#rangos).
- *color* y *emoji* están relacionados con cómo se muestran nuestros mensjaes. Ver [chat] (#chat).
- *last_online* y *last_chat_refresh* son campos que sirven para mostrar la lista de personas online y los mensajes desde el momento en que entramos al chat. Ver [lista de conectados] (#lista-de-conectados) y [chat] (#chat)
- *current_room* indica la sala donde se está chateando. Ver [chat] (#chat)
- *bot* sirve para marcar a los bots en la lista de usuarios. Ver (lista de conectados) [#lista-de-conectados].
- *can_change_username* será verdadero si el usuario tiene permitido cambiar su nick. Ver (perfil) [#perfil].

El registro es un sistema muy sencillo en el que se comprueba que el nombre de usuario tenga, al menos, 3 carácteres y la contraseña 8; y ue no contenga carácteres prohibidos. También se comprueba que el usuario no existiera con anterioridad.

```php
    $username = htmlentities($_POST["username"]);
    $password = md5(htmlentities($_POST["password"]));
    $mail = htmlentities($_POST["mail"]);

    $letras = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    $num = "0123456789";
    $symbols = "_.-";

    //Comprobamos que el username no tiene carácteres prohibidos
    for($i=0;$i<strlen($username);$i++){ 
        if (!strstr($letras,substr($username,$i,1)) and !strstr($num,substr($username,$i,1)) and !strstr($symbols,substr($username,$i,1))) {
            $error = '001x003';
        }
    } 
    
    //Comprobamos que tiene al menos 3 carácteres
    if (strlen($username) < 3 or strlen($_POST["password"]) < 8) {
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

```

Durante el Login se coteja en la base de datos y se comprueba que el nick y la contraseña coincidan con lo establecido. En el caso de que así sea se establecen algunas variables de sesión.

```php
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
```

Hay algunas funciones relacionadas con el sistema de usuarios que son utilizadas en diversos lugares de la aplicación para tareas muy variadas.

```php
//FUNCIÓN PARA COMPROBAR SI UN USUARIO EXISTE
function userExists($username) {
    $sql = "SELECT count(id) FROM `users` WHERE username = '".$username."'";
    $query = $GLOBALS['db']->query($sql);
    $resp = $query->fetch_array();

    if ($resp[0] > 0) {
        return true;
    } else {
        return false;
    }
}

//FUNCIÓN PARA COMPROBAR SI UN USUARIO ESTÁ EN LÍNEA
function userIsOnline($username) {
    $time = time()-60;

    $sql = "SELECT count(id) FROM `users` WHERE username = '".$username."' and last_online >= '".$time."'";
    $query = $GLOBALS['db']->query($sql);
    $resp = $query->fetch_array();

    if ($resp[0] > 0) {
        return true;
    } else {
        return false;
    }
}

//FUNCIÓN PARA DETERMINAR SI UN USUARIO TIENE PERMISO PARA ENTRAR A UNA SALA
function AllowedInRoom($userid, $room) {
    $myrole = $_SESSION["role"];
    $userid = $_SESSION["userid"];
    $roominfo = getRoomInfo($room);

    
    if (str_contains($roominfo['conditions'], 'only_newbies')) { //SI ES UNA NEWBIES ROOM
        if (isNewbie($userid) or isGuard($userid)) {
            return "ok";
        } else {
            return "003x002";
        }
    } elseif (str_contains($roominfo['conditions'], 'only_mods')) { //SI ES UNA GUARD ROOM
        if (isGuard($userid)) {
            return "ok";
        } else {
            return "003x001";
        }
    } elseif (str_contains($roominfo['conditions'], 'only_captain')) { //SI ES UNA CAPTAIN ROOM
        if (isCaptain($userid)) {
            return "ok";
        } else {
            return "003x004";
        }
    } 

    //ESPACIO RESERVADO PARA LAS SALAS PRIVADAS
    
    else {
        return "ok";
    }
}


//FUNCIÓN PARA COMPROBAR SI UN USARIO ES UN BOT
function isBot($username) {
    $sql = "SELECT bot FROM `users` WHERE username= '".$username."'";
    $query = $GLOBALS['db']->query($sql);
    $resp = $query->fetch_array();

    if ($resp[0] == 1) {
        return true;
    } else {
        return false;
    }
}

//FUNCIÓN PARA COMPROBAR SI UN USUARIO ES NEWBIE
function isNewbie($userid) {
    $sql = "SELECT count(msg) FROM `chat` WHERE uid= '".$userid."'";
    $query = $GLOBALS['db']->query($sql);
    $resp = $query->fetch_array();

    if ($resp[0] <= 100) {
        return true;
    } else {
        return false;
    }
}

//FUNCIÓN PARA COMPROBAR SI UN USUARIO ES GUARDIA
function isGuard($userid) {
    $sql = "SELECT role FROM `users` WHERE id = '".$userid."'";
    $query = $GLOBALS['db']->query($sql);
    $resp = $query->fetch_array();

    if ($resp['role'] > 1) {
        return true;
    } else {
        return false;
    }
}

//FUNCIÓN PARA COMPROBAR SI UN USUARIO ES CAPITÁN
function isCaptain($userid) {
    $sql = "SELECT role FROM `users` WHERE id = '".$userid."'";
    $query = $GLOBALS['db']->query($sql);
    $resp = $query->fetch_array();

    if ($resp['role'] > 3) {
        return true;
    } else {
        return false;
    }
}

//FUNCIÓN PARA OBTENER LOS DATOS DE UN USUARIO MEDIANTE SU ID
function getUserInfo($userid) {
    $sql = "SELECT * FROM `users` WHERE id = '".$userid."'";
    $query = $GLOBALS['db']->query($sql);
    $roominfo = $query->fetch_array();
    $return = array (
        'id' => $userid,
        'username' => $roominfo['username'],
        'mail' => $roominfo['mail'],
        'role' => $roominfo['role'],
        'last_online' => $roominfo['last_online'],
        'current_room' => $roominfo['current_room'],
        'color' => $roominfo['color'],
        'emoji' => $roominfo['emoji'],
        'can_move' => $roominfo['can_move'],
        'can_change_username' => $roominfo['can_change_username'],
        'last_chat_refresh' => $roominfo['last_chat_refresh']
    );

    return $return;
}

//FUNCIÓN PARA OBTENER EL ID DE ALGUIEN CON SU NOMBRE DE USUARIO
function getUserId($username) {
    $sql = "SELECT id FROM `users` WHERE username = '".$username."'";
    $query = $GLOBALS['db']->query($sql);
    $roominfo = $query->fetch_array();

    return $roominfo[0];
}
```