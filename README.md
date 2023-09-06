# ChatV1

CHAT V1 - ESTILO LYCOS CHAT, COMPLETAMENTE EN PHP + MYSQL + JS
Proyecto para el PORTAFOLIO de Jose Santiago
[https://chatv1.josesantiago.es](https://chatv1.josesantiago.es)

**INSTRUCCIONES DE INSTALACIÓN:**
 - La base de datos está en el archivo *BD_INSTALL.sql*
 - En *config.php* encuentras las variables para conectar con la base de datos.
 - El usuario con acceso de administrador por defecto tiene el username **GUARDIA** y la passsword **$%Fsjs$68**

Autor: Jose Santiago Muñoz

Inicio del proyecto: agosto 2023 | final del proyecto: septiembre 2023

## Índice
- [Introducción](#introducción)
- [Sistema de usuarios](#sistema-de-usuarios)
- [Chat](#chat)
- [Salas](#salas)
- [Bots](#bots)
- [Perfil](#perfil)
- [Rangos y comandos](#rangos-y-comandos)
- [Datos de interés](#datos-de-interés)

## Introducción
ChatV1 es un proyecto que nace de dos necesidades: la primera, la de añadir proyectos en el portafolio que puedan demostrar mis capacidades como programador PHP; y, la segunda, la de que este proyecto consistiera en sí mismo, un reto mental.

Esta idea está inspirada en el [Lycos/Yahoo Chat (En otros paises se llamaba WorldBiggestChat)](https://es.lycoschat.com/) que tuvo una gran popularidad entre el 2003 y el 2013. En resumidas cuentas, se trataba de un webchat programado en ASP (Active Server Pages) y basado en IRC (Internet Relay Chat) que tenía la particularidad de simular un barco (aunque de forma textual). En la actualidad ese chat sigue funcionando, y, aunque está muy cambiado, mantiene sus puntos más esenciales.

El reto mental de este proyecto, que indica las condiciones en las que ha sido desarrollado, es el siguiente:
1. Solo se pueden usar los lenguajes HTML5, CSS3, PHP, y MySQL. Se permite JavaScript únicamente en lo estrictamente necesario.
1. No se usará servidor IRC ni de ningún otro tipo de chat. Toda la arquitectura debe realizarse mediante querys MySQL, usando MySQLi de PHP.
1. Todo lo que se pueda hacer en el chat debe hacerse en la misma ventana (para simplificar el proyecto, ya que hay otro proyecto para el portafolio que consiste en un CMS).
1. No se pueden usar iframes, la misma página de chat debe controlar el flujo (sí se pueden usar includes).
1. Debe contener ciertos elementos de optimización automática de base de datos y autoregulación.

La idea tras estas normas era que fuera un proceso verdaderamente desafiante del que pudiera aprender ciertos apectos del PHP más puro.

## Sistema de Usuarios

Existe tanto un [login](https://chatv1.josesantiago.es/login.php) como un [registro de usuarios](https://chatv1.josesantiago.es/register.php).

Aunque de algunos de los campos de la base de datos hablaremos más adelante (*last_chat_refresh, current_room, color, o emoji* cuando hablemos del chat, *can_move* cuando hablemos de rangos y *can_change_username* cuando hablemos del perfil). Vamos a explicar algunos de los aspectos más importantes de este sistema de usuarios:

- *id* es la clave primaria de identificación de cada usuario.
- *username, mail* y *password* corresponden a los datos de acceso e identificación.
- *role* y *guard* son campos que indican el rango de la persona y si está o no de guardia. Ver [lista de conectados](#lista-de-conectados) y [rangos y comandos](#rangos-y-comandos).
- *color* y *emoji* están relacionados con cómo se muestran nuestros mensjaes. Ver [chat](#chat).
- *last_online* y *last_chat_refresh* son campos que sirven para mostrar la lista de personas online y los mensajes desde el momento en que entramos al chat. Ver [lista de conectados](#lista-de-conectados) y [chat](#chat)
- *current_room* indica la sala donde se está chateando. Ver [chat](#chat)
- *bot* sirve para marcar a los bots en la lista de usuarios. Ver (lista de conectados)[#lista-de-conectados].
- *can_change_username* será verdadero si el usuario tiene permitido cambiar su nick. Ver (perfil)[#perfil].

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

Lo primero que me pregunté es ¿Cómo puedo saber si alguien está o no conectado en un chat sin usar un servidor de chat? Lo que se me ocurrió es que cada vez que se actualizara algo en la página, se actualizara un campo en la base de datos *(last_online)* que contuviera un `time()`. De esa forma, si buscara en la base de datos las personas cuyo *last_time* fuera igual o menor al `time()` actual, con dos segundos se cortesía, sacaría una lista con personas conectadas.

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
- Por otra, la **muestra de mensajes en el cuadro de chat**. Esta última parte requería, igual que en la lista de usuarios, que se actualizara automáticamente. Además, debía tener en cuenta el color y el emoji escogidos en el [perfil](#perfil), mostrar el icono de bots y personas que escriben estando de guardia. Además, debían mostrarse allí a las alertas privadas de bots (respuestas a un comando, o alertas enviadaspor un guardia).

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
- *date* indica la fecha en la que se envía el mensaje utilizando `time()`
- *destiny* que indica public si es un mensaje normal, o el nombre de usuario de la persona a la que se dirije en caso de ser una alerta o un mensaje privado de bot.

Para evitar que la base de datos acomulara mensajes privados de bots, respuesta a comandos y ese tipo de mensajes temporales, programé un script muy sencillo que elimina los mensajes de esta índole después del tiempo (en segundos) establecido en la base de datos de configuración del chat Puedes obtener más información sobre esta base de datos en [datos de interés](#datos-de-interés).

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
```

En el código anterior puedes comprobar que se muestran solo los mensajes que se han publicado después del `time()` indicado en *last_chat_refresh* que es un campo en la tabla del usuario que indica, cuándo fue la última vez que se refrescó el chat. Ante eso es lógico preguntarse ¿Cuándo se actualiza el chat?

Existe una función que permite actualizar el *last_chat_refresh* tanto de un usuario concreto como de la propia persona.

```PHP
//FUNCIÓN PARA ACTUALIZAR LA LISTA DE MENSAJES QUE VISUALIZA UN USUARIO
function chatRefresh($user = false) {
    $time = time();

    if ($user) {
        $sql = "UPDATE `users` SET last_chat_refresh = '".$time."' WHERE username = '".$user."'";
    } else {
        $sql = "UPDATE `users` SET last_chat_refresh = '".$time."' WHERE username = '".$_SESSION["username"]."'";
    }

    $query = $GLOBALS['db']->query($sql);
    return true;
}
```

La idea detrás de esto es que el chat fuera actualizado siempre que se actualizara la página completamente o se cambiara de sala, pero esto resultó en un problema, ya que los cambios en el [perfil](#perfil), la visibilización de los mensajes privados, o la apertura del panel de oficiales (estas dos últimas son futuras implementaciones), actualizaban la página, así que añadí la siguiente comprobación:

```PHP
if (!isset($_GET["view"]) and !isset($_GET["return"])) {
    chatRefresh();
}
```

Si no existe una variable *view* del método *GET* (al abrir el perfil creamos en *GET* un `view=profile`, por ejemplo) y si tampoco existe la variable *return* del método *GET* (por ejemplo al pulsar el botón de volver en el perfil se crea en *GET* un `return=`), no se actualizaría el *last_chat_refresh*. En cualquier otro caso debe actualizarse. Esto funcionó bastante bien, ya que, a partir de ahora, solo se actualizaba el *last_chat_refresh* en el momento de entrada al chat, al entrar a una sala diferente o al ser movido de sala por un guardia. 

De esta forma, a cada usuario se le mostraban únicamente los mensajes nuevos desde que entraba a una sala nueva.

## Salas

Quería que hubiera varias salas donde poder hablar de varios temas, salas, además, que pudieran tener ciertos requisitos (salas para guardias, salas para capitanes) e incluso salas de calabozo (donde solo pueden entrar guardias, pero que sean el destino al que se mueve a las personas que rompen las reglas).

Inicié esta parte del proyecto creando la tabla en la base de datos que utilizaría para las salas y que tenía las siguientes filas:
- *id* como clave primaria, que sería el número identificador único de cada una de las salas.
- *orden* que es básicamente el orden en el que aparecen en el lobby.
- *name* es el nombre que aparece en la página de chat y en el lobby.
- *shortname* donde iría el nombre acortado de cada sala que es el que se usa en los comandos.
- *conditions* como en el caso de los chats, indica, separado por puntos y coma, las limitaciones o especificidades de cada sala. En este caso, se usa las siguientes conditions:
    - *movable*, que implica que se puede mover a usuarios a esa sala.
    - *no_movable*, que indica lo contrario a la anterior.
    - *only_newbies*, para novatos, es decir, personas que han mandado menos de 100 mensajes (los guardias se saltan esta limitación).
    - *only_mods*, que implica que es una sala de acceso únicamente a guardias.
    - *only_captains*, que implica que es una sala de acceso únicamente permitido a capitanes.

Una vez creada la lógica de chat, lo siguiente sería asociar a cada usuario a una sala. De inicio, y al loguearse en el ChatV1 se establece la sala de cada persona en el Lobby, lo cual deriva hacia lobby.php donde se debe seleccionar la sala a la que se quiere entrar.

```PHP
if ($password === $userinfo['password']) { //La contraseña es correctisima
    //Actualizamos el last_online
    updateLastOnline($username, time());

    //Creamos la variable de sesión
    $_SESSION["username"] = $username;
    $_SESSION["userid"] = $userinfo['id'];
    $_SESSION["role"] = $userinfo['role'];
    $_SESSION["chatroom"] = 'lobby';
    header("Location: lobby.php");
}
```

```PHP
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
} else {
    //header("Location: ");
    header("Location: lobby.php");
}
```

Siempre que se entre al Lobby (porque se pulse el botón de "Ir al Lobby" dentro del chat) debe establecerse también como sala el mismo lobby.

```PHP
$_SESSION["chatroom"] = "lobby";
$sql = $db->query("UPDATE `users` SET current_room = 'lobby' WHERE id = '".$_SESSION["userid"]."'");
```

Cuando se selecciona una sala en el Lobby se creaa una variable *GET* llamada *goroom*. Se comprueba que la sala exista y que haya permiso para entrar en ella. Si hay permiso se mueve a la sala, si no, se vuelve al Lobby y se muestra el motivo por el que no se puede acceder a la sala (gracias a la función `AllowedInRoom()` que hemos visto en el [sistema de usuarios](#sistema-de-usuarios))

```PHP
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
```

Una vez dentro de cada sala se mostrarán únicamente en la lista de usuarios las personas que hay conectadas en esa sala como hemos visto en el [sistema de usuarios](#sistema-de-usuarios) y los mensajes que se envían en esa sala en concreto, como también hemos visto en la sección de [chat](#chat).

Cada sala tiene su propio estilo css para que la experiencia en cada sala sea distinta.

```PHP
if (file_exists('css/room_styles/'.$_SESSION["chatroom"].'.css'))  {
    echo '<link href="css/room_styles/'.$_SESSION["chatroom"].'.css" rel="stylesheet">';
} 
```

Normalmente una persona puede moverse pulsando el botón de "Ir al Lobby", aunque, en las ocasiones en las que un guardia utiliza el comando */capturar* para mover a un usuario, éste perderá la capacidad de moverse de sala hasta que se le vuelva a mover con el comando */llevar*, esto lo veremos mejor en la sección de [rangos y comandos](#rangos-y-comandos).

```PHP
if (getUserInfo($_SESSION["userid"])['can_move'] == 1) {
    echo '<p><input type="submit" name="lobby" value="Ir al Lobby" class="menu_button" /></p>';
} else {
    echo '<p><input type="submit" name="lobby" value="Ir al Lobby" class="menu_button" disabled /></p>';
}
```

Los guardias, como veremos de forma más específica en la sección de tienen varios poderes para manejar dónde están los usuarios: en concreto los comandos */capturar* y */llevar*. Estos comandos lo que hacen es cambiar en la base de datos el campo *current_room* de alguien concreto en la tabla de usuarios. La diferencia entre ellos es que */capturar* pide que alguien pueda moverse de sala, mientras que */llevar* permite el movimiento.

```PHP
case '/capturar':
    if ((!isset($arrg[1]) or $arrg[1] == "") or (!isset($arrg[2]) or $arrg[2] == "")) {
        botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Para mover a alguien y evitar que se mueva el comando es /capturar {nick} {shortname_room}.",$_SESSION["username"]);
    } else {
        if (userExists($arrg[1])) {
            if (userIsOnline($arrg[1])) { 
                if (roomExists($arrg[2])) {
                    $sql = "UPDATE `users` SET current_room = '".$arrg[2]."', can_move = '0' WHERE username = '".$arrg[1]."'";
                    $query = $GLOBALS['db']->query($sql);
                    botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Acabas de mover a ".$arrg[1]." a la sala ".getRoomInfo($arrg[2])['name'].".",$_SESSION["username"]);
                } else {
                    botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! La sala ".$arrg[2]." no existe, puedes comprobar en el panel el shortname de todas las salas (este shortname es el que debes usar en el comando).",$_SESSION["username"]);
                }
            } else {
                botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! ".$arrg[1]." no está conectado, así que no puedes moverle.",$_SESSION["username"]);
            }
        } else {
            botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! no encuentro a ".$arrg[1]." ¿Estás seguro de que existe?.",$_SESSION["username"]);
        }
    }
    break;
    case '/llevar':
    if ((!isset($arrg[1]) or $arrg[1] == "") or (!isset($arrg[2]) or $arrg[2] == ""))  {
        botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Tienes que decirme a quién mover y a dónde. Usa el comando así: /liberar {nick} {shortname_room}.",$_SESSION["username"]);
    } else {
        if (userExists($arrg[1])) {
            if (userIsOnline($arrg[1])) {
                if (roomExists($arrg[2])) {
                    $sql = $sql = "UPDATE `users` SET current_room = '".$arrg[2]."', can_move = '1' WHERE username = '".$arrg[1]."'";
                    $query = $GLOBALS['db']->query($sql);
                    botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Acabas de mover a ".$arrg[1]." a la sala ".getRoomInfo($arrg[2])['name'].".",$_SESSION["username"]);
                } else {
                    botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! La sala ".$arrg[2]." no existe, puedes comprobar en el panel el shortname de todas las salas (este shortname es el que debes usar en el comando).",$_SESSION["username"]);
                }
            } else {
                botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! ".$arrg[1]." no está conectado, así que no puedes moverle.",$_SESSION["username"]);
            }
        } else {
            botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! no encuentro a ".$arrg[1]." ¿Estás seguro de que existe?.",$_SESSION["username"]);
        }
    }
    break;
```

Para poder hacer efectivo en el chat este cambio lo que hice fue aprovechar la actualización cada segundo de [la lista de usuarios conectados](#lista-de-conectados) (aunque podría haber usado el [chat](#chat) igualmente) para hacer una comproobación de la sala que aparece en la base de datos. En caso de que la *current_room* de la base de datos y la de la variable de sesión *chatroom* sean distintas, esto implicará, en absolutamente todos los casos, que se ha movido de sala a la perona.

En este caso se igualaría la variable de sesión y se dirigiría la página directamente a *chat.php* con la intención de mostrar la nueva sala y actualizar siempre el *last_chat_refresh* (la razón por la que desde *chat.php* vamos a *chat.php* en vez de actualizar la página es eliminar los posibles *$_GET["return]* y asegurarnos de que se actualiza el *last_chat_refresh*)

```PHP
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
```

## Bots

Los bots eran uno de los aspectos más característicos del chat de Lycos en el que está inspirado este proyecto. Tenían una triple función:
- Aparecían en salas características, saludaban al entrar y al usuario y tenían algunas frases de conversación como respuesta a algunas claves concretas.
- Eran la forma en la que el chat te daba feedback de lo que hacías: como respuesta a los comandos, como saludo, si recibías un mensaje privado, etc.
- Era la forma en la que un moderador te podía alertar de algo.

La idea era retomar ese parte del chat y programarla en PHP. Comencé creando los bots como usuarios en la tabla `users` (y tal y como hemos visto en el [sistema de usuarios](#sistema-de-usuarios) tienen un valor 1 en el campo bot). 

Cuando se actualiza el last_online de una persona se actualiza también el last_online de un bot. De esa forma, siempre aparecen conectados en la sala en la que deberían estar.

Cuando alguien entra a una sala, el bot de la sala suele enviar uno de sus varios saludos característicos.

```PHP
//Si hay un bot en la sala, manda un saludo.
$sqlbot = "SELECT id FROM `users` WHERE current_room = '".$room."' and bot = '1'";
$querybot = $db->query($sqlbot);
$respbot = $querybot->fetch_array();
$num = $querybot->num_rows;

if ($num > 0) {
    $sqlsaludos = "SELECT * FROM `saludos` WHERE botid = '".$respbot['id']."' ORDER BY RAND()";
    $querysaludo = $db->query($sqlsaludos);
    $saludos = $querysaludo->fetch_array();

    $saludo = str_replace('{nick}',$_SESSION["username"],$saludos);

    $date = time()+5;

    $sqlmsg = "INSERT INTO `chat` (uid, rid, msg, conditions, date, destiny) VALUES('".$respbot[0]."', '".$resp["id"]."','".$saludo['msg']."','public_bot','".$date."','public')";
    $insertmsg = $db->query($sqlmsg);
}
```

Para la respuesta a los comandos, existe una función que añade una alerta o un mensaje privado de un bot, que, como ya se expuso en [chat](#chat) se ven de una forma muy concreta, y mostrando el avatar del bot.

```PHP
//FUNCIÓN QUE REGISTRA UN PRIVATE BOT MSG EN UNA SALA CONCRETA
function botPrivateMsg($botid, $rid, $msg, $destiny) {
    $sql = "INSERT INTO `chat` (uid, rid, msg, conditions, date, destiny) VALUES('".$botid."', '".$rid."', '".$msg."','private_bot','".time()."', '".$destiny."')"; 
    $query = $GLOBALS['db']->query($sql) or die($GLOBALS['db']->error);
    return true;
}
```

Para las alertas, existe el comando */alertar* que básicamente permite a los guardias añadir un *botPrivateMsg* (hablaremos más en profundidad de los comandos en [rangos y comandos](#rangos-y-comandos))

```PHP
case '/alertar':
    if ((!isset($arrg[1]) or $arrg[1] == "") or (!isset($arrg[2]) or $arrg[2] == "") or (!isset($arrg[3]) or $arrg[3] == ""))  {
        botPrivateMsg(3,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Algo está mal. Debes usar el comando de la siguiente forma <b>/alertar {nick} {botid} {mensaje}. El el panel tienes información sobre los botid.",$_SESSION["username"]);
    } else {
        if (userExists($arrg[1])) {
            if (userIsOnline($arrg[1])) {
                $botname = getUserInfo($arrg[2])['username'];
                if (isBot($botname)) {
                    $uid = getUserId($arrg[1]);
                    $room = getUserInfo($uid)['current_room'];
                    $rid = getRoomInfo($room)['id'];
                    $user = $arrg[1];
                    $bot = $arrg[2];
                    
                    //eliminados todo lo que no sea mensaje
                    unset($arrg[0]);
                    unset($arrg[1]);
                    unset($arrg[2]);

                    $msg = implode(" ",$arrg);

                    botPrivateMsg($bot, $rid, $msg, $user);
                    botPrivateMsg($bot,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Mensaje enviado: ".$msg,$_SESSION["username"]);
                } else {
                    botPrivateMsg(3,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! El id seleccionadop no es de un bot. Recuerda que en el panel tienes todos los botid.",$_SESSION["username"]);
                }
            } else {
                botPrivateMsg(3,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! No puedo mandar la alerta a ".$arrg[1]." porque no está conectado",$_SESSION["username"]);
            }
        } else {
            botPrivateMsg(3,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! No puedo mandar la alerta a ".$arrg[1]." porque no el usuario no existe",$_SESSION["username"]);
        }
    }
```

## Perfil

Al hacer click en el botón de Mi Perfil accedes a los dos paneles donde es posible cambiar alguna información personal de tu perfil como el nombre de usuario, el email o la contraseña.

El script aquí tiene una lógica tan sencilla como comprobar si ha habido algún cambio y cuál ha sido, en base a eso hace las comprobaciones pertinentes y ejecuta el cambio. En el caso de haber cambiado el nombre de usuario, además, te desconecta para obligarte a reloguearte y que así todos los cambios se hagan efectivos. Esto tiene una doble función: por un lado evita que algunas funciones que toman como parámetro el nombre de usuario causen errores, por otro lado, actualiza las variables de sesión.

```PHP
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
```

De inicio, en el formulario aparece desactivada la opción de cambiar el nombre de usuario. Para que sea posible cambiar este dato, un guardia debe usar el comando */cambionombre* que está disponible para tenientes (tendrás más información sobre este rango en la sección de [rangos y comandos](#rangos-y-comandos))

```PHP
if ($userinfo['can_change_username'] == 0) {
    echo '<td><input type="text" name="username" value="'.$_SESSION["username"].'" disabled />';
    echo '<input type="hidden" name="username" value="'.$_SESSION["username"].'" />';
} else {
    echo '<td><input type="text" name="username" value="'.$_SESSION["username"].'" />';
}
```
```PHP
case '/cambionombre':
    if (!isset($arrg[1]) or $arrg[1] == "") {
        botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Tienes qué decirme a quién quieres permitir que se cambie el nombre con /cambionombre {nick}.",$_SESSION["username"]);
    } else {
        if (userExists($arrg[1])) {
            $sql = "UPDATE `users` SET can_change_username = '1' WHERE username = '".$arrg[1]."'";
            $query = $GLOBALS['db']->query($sql);
            botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! ¡Hecho! Ahora ".$arrg[1]." podrá cambiar su nombre de usuario. No olvides dejar una nota en la ficha del usuario.",$_SESSION["username"]);
        } else {
            botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Ese nombre de usuario no existe. Comprueba el nick que has escrito.",$_SESSION["username"]);
        }
    }
    break;
```

En el menú de la derecha, existe la opción de configurar las opciones de chat que ya hemos mencionado en el [sistema de usuarios](#sistema-de-usuarios) y en [chat](#chat). Básicamente son dos opciones:
- El color del nombre, que configura el color en el que aparece tu nombre de usuario en el chat.
- El emoji, que aparecerá al lado de tu nombre de usuario en el chat.

```PHP
if (isset($_POST["change_chat_options"])) {
    $color = $_POST["color"];
    $emoji = $_POST["emoji"];

    $sql = "UPDATE `users` SET color = '".$color."', emoji = '".$emoji."' WHERE id = '".$_SESSION["userid"]."'";
    $query = $db->query($sql);

    $alert = "Se han realizado los cambios correctamente";
}
```

## Rangos y comandos

No voy a mentir. Cuando inicié este proyecto una de las funcionalidades que más me apetecía programar eran los comandos. Recuerdo que cuando entraba a Lycos Chat y veía allí a los oficiales (lo que aquí son los guardias) siempre trataba de imaginar qué comandos tenían y qué poderes tenían. La realidad es que podría haberme dedicado a hacer cientos de estos, y estoy seguro de que acabaré añadiendo muchos de ellos por simple gusto, aunque, de inicio, he tratado de ser coherente.

Los rangos y los comandos van de la mano, el rol o el rango que un usuario tiene define, entre otras muchas cosas, los permisos que tiene para entrar a determinadas salas, la posibilidad o no de abrir el panel de guardia (que aún está en desarrollo) y la cantidad de comandos que puede realizar.

***Existen 5 rangos en el chat que, en la base de datos, están numerados del 0 a 4:***
- 0: El rango por defecto y determina el usuario corriente sin ningún tipo de permisos especiales.
- 1: Rango reservado para el sistema de vip (que aún no está implementado) y que permitirá crear salas privadas y entrar a salas llenas, de newbies y establecidas como vip_only.
- 2: Guardia, es el primer nivel de moderación y tiene los comandos básicos de moderación del chat y salas establecidas como moderation_only.
- 3: Teniente, es el segundo nivel de moderación y el más alto. Tiene acceso a todo lo que implica el nivel de guardia y además, se le brinda acceso a algunos comandos adicionales que requieren un mayor nivel de responsabilidad.
- 4: Capitán, es el nivel de administración. Por defecto el usuario GUARDIA tiene nivel de administración. Este nivel tiene acceso a todos los permisos y a las salas establecidas como captain_only.

Los rangos son ascendentes, es decir, que los rangos con una numeración mayor tienen acceso a todos los permisos de la numeración menor (los guardias tienen acceso a los permisos vip, los capitanes a los comandos de tenientes, etc).

Existen varias funciones para comprobar si un usuario tiene un nivel concreto:

```PHP
//FUNCIÓN PARA COMPROBAR SI UN USUARIO ES VIP
function isVip($userid) {
    $sql = "SELECT role FROM `users` WHERE id = '".$userid."'";
    $query = $GLOBALS['db']->query($sql);
    $resp = $query->fetch_array();

    if ($resp['role'] > 0) {
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

//FUNCIÓN PARA COMPROBAR SI UN USUARIO ES TENIENTE
function isTenient($userid) {
    $sql = "SELECT role FROM `users` WHERE id = '".$userid."'";
    $query = $GLOBALS['db']->query($sql);
    $resp = $query->fetch_array();

    if ($resp['role'] > 2) {
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
```

Es posible ver los guardias, tenientes y capitanes de guardia usando el comando */guardias*

```PHP
case '/guardias':
    $time = time()-60;
    $sql = "SELECT * FROM `users` WHERE guard = '1' and last_online >= '".$time."'";
    $query = $GLOBALS['db']->query($sql);

    if ($query->num_rows > 0) {
        $guards = ' ';
        while ($resp = $query->fetch_array()) {
            $guards .= "<b>".$resp['username']."</b> que está en la sala <i>".getRoomInfo($resp['current_room'])['name']."</i>, ";
        }

        botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Actualmente hay ".$query->num_rows." oficiales de guardia:".$guards." puedes escribirle(s) un mensaje privado o seguirle(s) con /seguir si necesitas algo.",$_SESSION["username"]);
    } else {
        botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Lamento decirte que ahora mismo no hay oficiales de guardia.",$_SESSION["username"]);
    }
    break;
```

Dentro de *comandos.php* está toda la lógica de programación de éstos y se divida en tres partes:
- Función `commands()` que devuelve un array de comandos, donde aparecen todos éstos, el rango mínimo para realizarlos y la descripción y la sintaxis de cada comando.
- Funciones de comandos. Son básicamente 4: 
    - `isCommand()` comprueba si un mensaje es un comando y si éste existe
    - `executeCommand()` que es una función muy similar a la anterior pero que llama la función de ejecución
    - `doExecuteCommand()` que ejecuta el comando concreto
    - `incorrectCommand()` crea un bot_private informando de los errores encontrados ejecutando un comando concreto

La lógica para separar, por ejemplo, `isCommand()` y `executeCommand()` es la necesidad de tener un valor voleano que confirme que un mensaje es un comando válido sin ejecutarlo, y otra que hiciese la misma comprobación pero llamando a una función de ejecución. La función `doExecuteCommand()` está separada de `executeCommand()` principalmente por motivos de lectura ya que la función `doExecuteCommand()` es muy grande, ya que contiene todos los comandos.

```PHP
//FUNCIÓN QUE DEVUELVE UNA ARRAY CON TODOS LOS COMANDOS Y EL NIVEL NECESARIO PARA USARLOS
function commands() {
    $commands = array(
        '/cambionombre' => array(3,'/cambionombre {username}','Permite que el usuario pueda cambiar su nombre de usuario'),
        '/guardia' => array(2,'/guardia','Activa o desactiva la guardia. Mientras estás de guardia aparece el 🎖️ junto a tu nombre de usuario en la lista de conectados y en el chat. Además apareces, junto con la sala en la que estás cuando la gente utiliza el comando /guardias'),
        '/llevar' => array(2,'/llevar {username} {room_shortname}','Mueve de sala a un usuario. El usuario puede volver a cambiar de sala'),
        '/capturar' => array(2,'/capturar {username} {room_shortname}','Mueve de sala al usuario y le desactiva la posibilidad de cambiar de sala. Para que el usuario pueda volver a moverse de sala, tienes que moverle con el comando /llevar'),
        '/info' => array(2,'/info {username}','Comprueba si alguien está registrado con ese nick, y si está o no conectado'),
        '/alertar' => array(2,'/alertar {username} {botid} {msg}','Alerta al usuario, mandando un mensaje de bot (del estilo private_bot_message, en el que aparece la imagen del bot'),
        '/comandos' => array(0,'/comandos','Te cita la lista de comandos que puedes usar con tu rango actual'),
        '/quien' => array(0,'/quien {username}','Te devuelve si la persona es newbie o no y si es guardia o no'),
        '/rango' => array(0,'/rango','Te devuelve tu rango [En la actualidad te dice tu rango de usuario, pero la idea es que te devuelva más adelante tu rango (entre los basados en mensajes)]'),
        '/guardias' => array(0,'/guardias','Devuelve todas las personas que se encuentran en modo guardia así como su ubicación'),
        '/seguir' => array(0,'/seguir {username}','Te mueve hacia la sala donde está el usuario')
    );

    return $commands;
}
```

```PHP
//FUNCIÓN QUE IDENTIFICA LOS COMANDOS Y COMPRUEBA SI ESTOS EXISTEN
function isCommand($msg) {
    $commands = commands();

    //Si empieza por / es un comando
    if (substr($msg,0,1) == '/') {
       //Separamos el comando de los argumentos
       $arrg = explode(" ", $msg);
       
       //Comprobamos que existe el comando
       if (isset($commands[$arrg[0]])) {
            if ($_SESSION["role"] >= $commands[$arrg[0]][0]) {
                return true;
            } else {
                incorrectCommand();
                return true;
            }
       } else {
        incorrectCommand();
        return true;
       } 
    } else {
        return false;
    }
}

//FUNCIÓN DE PASO PARA COMPROBAR Y EJECUTAR EL doExecuteCommand
function executeCommand($msg) {
    $commands = commands();

    //Si empieza por / es un comando
    if (substr($msg,0,1) == '/') {
       //Separamos el comando de los argumentos
       $arrg = explode(" ", $msg);
       
       //Comprobamos que existe el comando
       if (isset($commands[$arrg[0]])) {
            if ($_SESSION["role"] >= $commands[$arrg[0]][0]) {
                doExecuteCommand($arrg);
                return true;
            } else {
                return false;
            }
       } else {
        return false;
       } 
    } else {
        return false;
    }
}
```

No voy a dejar aquí todo el `doExecuteCommands()` porque una parte de esos comandos se han ido publicando en sus zonas correspondientes (por ejemplo, la función */cambionombre* en está en [perfil](#perfil)). Basicamente esa función es un switch que comprueba el comando y hay un case para cada uno de ellos, en base a eso ejecuta toda la lógica de programación de cada uno de ellos

```PHP
function doExecuteCommand($arrg) {
    switch ($arrg[0]) {
        case '/comando1':
            //Lógica de programación del comando1 sus argumentos serán $arrg[1...n]
            break;
        case '/comando2':
            //Lógica de programación del comando2 sus argumentos serán $arrg[1...n]
            break;
         case '/comando2':
            //Lógica de programación del comando2 sus argumentos serán $arrg[1...n]
            break;
    }
}
```

Hay un comando que requiere especial atención, y es, básicamente, el que permite ver los comandos de los que se tiene permiso para ejecutar

```PHP
case '/comandos':
    $commands = commands();
    $commandlist = " ";
    foreach ($commands as $clave => $valor) {
        if ($valor[0] <= $_SESSION["role"]) {
            $commandlist .= "<b>".$clave."</b> ";
        }
    }

    botPrivateMsg(3,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Los comandos que puedes utilizar son:".$commandlist." ¡Espero haber sido de ayuda!",$_SESSION["username"]);
    break;
```

## Datos de interés

Uno de los objetivos que tenía cuando inicié el proyecto es crear algo que, en la medida necesaria, fuera autoregulable y que guardara logs de una gran variedad de aspectos.

Uno de los aspectos más relevantes es la configuración dinámica (que se guarde en la base de datos y que, por tanto, fuera configurable en la parte del panel de guardias dedicada a capitanes). La forma en la que la planteé se basaba en la existencia de dos tipos de datos distintos en las configuraciones: datos numéricos (int) y cadenas de texto (str). En base a eso diseñé una tabla en la base de datos con los siguientes campos:
- *name*, que incluiría el nombre del campo
- *str_content*, que incluiría el dato, siempre que fuera una cadena de texto
- *int_content*, que incluiría el dato, siempre que fuera un dato numérico

Para entender cómo funciona vamos a poner dos ejemplos: el nombre del chat, por ejemplo, y los segundos tras los que el sistema debe borrar las alertas privadas de bots de la base de datos. En el primero el dato que se tiene que configurar es una cadena de texto, el *name* de ese dato de configuración sería "La Forja", y esa cadena de texto aparece en *str_content*; en el segundo ejemplo, el dato es numérico, imagina que lo configuramos para se borre tras 7200 segundos, en ese caso en el *name* aparecería *private_botmsg_del* y en *int_content* aparecería 7200.

Existe una función llamada `getConfig()` con la que podemos obtener algunos aspectos de la configuración que son demandados en el código. 

```PHP
// FUNCIÓN PARA OBTENER CIERTOS DATOS DE CONFIGURACIÓN QUE ESTÁN EN LA BASE DE DATOS
function getConfig($type, $v) {
    if ($type == "str") {
        $dbq = $GLOBALS['db']->query("SELECT str_content FROM `config` WHERE name = '".$v."'") or die($GLOBALS['db']->error);
        $dbr = $dbq->fetch_object();
        $return = $dbr->str_content;
        $error = false;
    } elseif ($type == "int") {
        $dbq = $GLOBALS['db']->query("SELECT int_content FROM `config` WHERE name = '".$v."'") or die($GLOBALS['db']->error);
        $dbr = $dbq->fetch_object();
        $return = $dbr->int_content;
        $error = false;
    } else {
        $error = true;
        return(false);
    }

    if (!$error) {
        if ($dbq->num_rows > 0) {
            return($return);
        } else {
            return(false);
        }
    }
}
```

Esto está muy bien, pero vamos a ser prácticos... ¿Dónde se usa y para qué? El primer elemento de este tipo que desarrollé fue un pequeño script que optimizara las tablas de la base de datos cada cierto tiempo. Básicamente, en la table `config` se guarda una entrada con nombre *last_optimize* que incluye el `time()` de la última vez que se optimizó. Si han pasado 604800 segundos o más (es decir, una semana) desde el último *last_optimize* se ejecuta un `OPTIMIZE TABLE` en todas las tablas de la base de datos.

```PHP
// OPTIMIZACIÓN ATOMÁTICA DE LA BASE DE DATOS
$actualTime = time();
$last_optimize = getConfig('int', 'last_optimize');
$next_optimize = $last_optimize + 604800;

if ($actualTime >= $next_optimize) {
    $sql = "OPTIMIZE TABLE `chat`, `config`, `logs`, `rooms`, `saludos`, `users`";
    $db->query($sql) or die($db->error);
    $sql2 = "UPDATE `config` SET int_content = '".$actualTime."' WHERE name = 'last_optimize'";
    $db->query($sql2) or die($db->error);
    addInLog("Root", "Se ha optimizado automáticamente la base de datos");
}
```

Como ya habíamos mencionado anteriormente, esta función también se usa para borrar los mensajes de los bots después de cierto tiempo o para el título y el subtítulo del cha que aparecen en las etiquetas `<title>`.

Existe también un log en el que se guardan ciertos aspectos como el uso de comandos, la optimización de la base de datos, y, cuando se implemente, el uso del panel de guardia. Se encuentra en la tabla `logs` de la base de datos y tiene la siguiente estructura:
- *id*, como primary key identificativa de cada entrada.
- *date*, donde se guarda un `time()` del momento del registro.
- *ip* de la persona que ejecuta la acción guardada.
- *who* con dos opciones:
    - *Root* en el caso de acciones automatizadas como la optimización de la base de datos.
    - El nombre de usaurio de la persona que realiza la acción, como por ejemplo, GUARDIA si ha activado el cambiar de nick a alguna persona.
- *action* donde se explicita la acción, algunos ejemplos son "Se ha optimizado automáticamente la base de datos" o "Activa en el usuario con uid 3 el cambio de nombre".

Estos registros se añaden mediante la función `addInLog()`

```PHP
// FUNCIÓN QUE AÑADE UNA ENTRADA AL LOG DE LA BASE DE DATOS
function addInLog($who, $action) {
    $date = time();
    $ip = $_SERVER['REMOTE_ADDR'];
    $sql = "INSERT INTO `logs` (date, ip, who, action) VALUES('".$date."', '".$ip."', '".$who."', '".$action."')";

    $inserting = $GLOBALS['db']->query($sql) or die($GLOBALS['db']->error);
}
```