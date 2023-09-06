# ChatV1

CHAT V1 - ESTILO LYCOS CHAT, COMPLETAMENTE EN PHP + MYSQL + JS
Proyecto para el PORTAFOLIO

Autor: Jose Santiago Muñoz
Inicio del proyecto: agosto 2023, final del proyecto: septiembre 2023

## Índice
- [Introducción] (#introducción)
- [Sistema de usuarios] (#sistema-de-usuarios)
- [Chat] (#chat)

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


## Lista de Conectados

Cuando me enfrenté al reto de hacer una lista de usuarios que mostrara a todas las personas online en tiempo real en un lenguaje como PHP, y, tras probar algunas ideas que no funcionaron, llegué a la conclusión de que iba a tener que usar Javascript.

Lo primero que me pregunté es ¿Cómo puedo saber si alguien está o no conectado en un chat sin usar un servidor de chat? Lo que se me ocurrió es que cada vez que se actualizara algo en la página, se actualizara un campo en la base de datos *(last_online)* que contuviera un *time()*. De esa forma, si buscara en la base de datos las personas cuyo *last_time* fuera igual o menor al *time()* actual, con dos segundos se cortesía, sacaría una lista con personas conectadas.

```PHP
            $time = time()-2;

            $sql = "SELECT username FROM `users` 
            WHERE current_room = '".$_SESSION['chatroom']."' 
            AND username != '".$_SESSION["username"]."' 
            AND last_online >= '".$time."' 
            ORDER BY username ASC";
            $query = $db->query($sql);
```

Lo siguiente fue preguntarme ¿Cómo actualizo constantemente el *last_online*? Lo que se me ocurrió fue una doble solución: Por un lado, actualizaría el *last_online* propio cada vez que se actualizara la página. También hice que el script actualizara automáticamente el last_online de los bots, y así me aseguraba de que siempre estuvieran conectados en las salas en las que aparecían.

```PHP
if (!isset($_SESSION["username"])) {
    echo '<script type="text/JavaScript"> location.assign("login.php"); </script>';
} else {
    updateLastOnline($_SESSION["username"], time());
    updateLastOnline("_bots", time());
}
```

Por otro lado debia actualizar constantemente la lista de usuarios conectados que estaba mostrando. Para ello, efectivamente, tuve que usar JavaScript y crear un script que actualizara, cada segundo, el *div* donde estaba incluído el userlist con la página en PHP donde se mostraba la lista de usuarios.

```HTML
 <script>
            $(document).ready(function() {
                var refreshId =  setInterval( function(){
                    $('#left_column').load('usrlist.php'); //Se actualiza el div
                }, 1000 );
            });
        </script>
        <div class="left_column" id="left_column">
```

Con todo esto ya tenía una lista de usuarios que se actualizaba automáticamente y que además actualizaba mi propio estado a online. Hice que mostrara primero el usuario propio, y después el resto de usuarios. Ya sólo faltaba un detalle: mostrar un emoji en la lista si una persona eraun moderador de guardia, y otro emoji distinto si la persona era un bot:

```PHP
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
```

## Chat

A la hora de imaginar cómo iba a abordar el chat propiamente dicho lo dividí en dos funcionalidades distintas que tenía que idear y programar:

- Por una parte, el **envío de mensajes**.
- Por otra, la **muestra de mensajes en el cuadro de chat**. Esta última parte requería, igual que en la lista de usuarios, que se actualizara automáticamente. Además, debía tener en cuenta el color y el emoji escogidos en el [perfil] (#perfil), mostrar el icono de bots y personas que escriben estando de guardia. Además, debían mostrarse allí a las alertas privadas de bots (respuestas a un comando, o alertas enviadaspor un guardia).

A la hora de crear la tabla en la base de datos de mensajes ideé los siguientes campos:
- *id* como identificador y clave primaria que identifica cada mensaje.
- *uid* como id de usuario. Clave foránea que se corresponde con la id del usuario que manda el mensaje.
- *rid* como id de sala. Clave foránea que se corresponde con la id de la sala en la que se tiene que mostrar este mensaje.
- *msg* que es el texto del mensaje propiamente dicho.
- *conditions* es la decisión que tomé para distinguir el tipo de mensaje que era, y, por tanto, cómo se debía ver. Lo dejé como un *varchar* que tuviera los elementos, separados por puntos y comas, que identificaran la naturaleza del mensaje. Entre las opciones que se tendrán en cuenta en el programa están:
    - *guard_on* indica que el mensaje se ha enviado por un moderador con el estado de guardia, y debe mostrar un emoji específico al lado del nombre.
    - *public_bot* es un mensaje público de un bot, que muestra el emoji del bot al lado del nombre.
    - *private_bot* indica que se trata de una alerta o de una respuesta privada de un bot. Es la forma en la que el chat informa de cualquier cosa (respuesta de comandos, por ejemplo).
    - *normal* el cual está siempre por defecto.
- *date* indica la fecha en la que se envía el mensaje utilizando *time()*
- *destiny* que indica public si es un mensaje normal, o el nombre de usuario de la persona a la que se dirije en caso de ser una alerta o un mensaje privado de bot.

Para evitar que la base de datos acomulara mensajes privados de bots, respuesta a comandos y ese tipo de mensajes temporales, programé un script muy sencillo que elimina los mensajes de esta índole después del tiempo (en segundos) establecido en la base de datos de configuración del chat Puedes obtener más información sobre esta base de datos en [datos de interés] (#datos-de-interés).

```PHP
//Borrado de los mensajes private_bot que pasaran el tiempo estimado en la base de datos
$seconds = getConfig('int','private_botmsg_del');
$query = $db->query("DELETE FROM `chat` WHERE conditions = 'private_bot' and date <= '".time()-$seconds."'");
```

A la hora de enviar mensajes la lógica es muy sencilla. Se comprueba que el mensaje no esté vacío, y si no lo está, se comprueba que no sea un comando. Si ambas condiciones son falsas, se comprueba si la persona está de guardia (para saber si tiene esa *condition*) y luego se introduce ese mensaje en la base de datos.

```PHP
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
```

La mecánica para que se actualizaran automáticamente los mensajes fue la misma que con la lista de usuarios, pero añadiendo una línea para que el *scroll* quedase siempre en la parte de abajo de ese cuadro (para que siguiera la lógica natural de un chat, que nos lleva a mirar justo encima de la barra de escritura de mensajes).

```HTML
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
```

Finalmente para mostrar los mensajes, se evalúan las *conditios*  y en base a eso se muestra de una forma u otra.

```PHP
$sql = "SELECT * FROM `chat` WHERE rid = '".getRoomInfo($_SESSION['chatroom'])['id']."' and date > '".getUserInfo($_SESSION["userid"])['last_chat_refresh']."' ORDER BY ID ASC"; //Añadir and date >= '".$time."'
             $query = $db->query($sql);

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
```