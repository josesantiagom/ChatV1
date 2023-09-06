# ChatV1

CHAT V1 - ESTILO LYCOS CHAT, COMPLETAMENTE EN PHP + MYSQL + JS
Proyecto para el PORTAFOLIO

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

## Introducción
ChatV1 es un proyecto que nace de dos necesidades: la primera es la necesidad de añadir proyectos en el portafolio que puedan demostrar mis capacidades como programador PHP, y, la segunda, la necesidad de que este proyecto consistiera en sí un reto mental.

Esta idea está inspirada en el [Lycos/Yahoo Chat (En otros paises se llamaba WorldBiggestChat)](https://es.lycoschat.com/) que tuvo una gran popularidad entre el 2003 y el 2013. En resumidas cuentas, se trataba de un webchat programado en ASP (Active Server Pages) y basado en IRC (Internet Relay Chat) que tenía la particularidad de simular un barco (aunque de forma textual). En la actualidad ese chat sigue funcionando, y, aunque está muy cambiado, mantiene sus puntos más esenciales.

El reto mental de este proyecto, que indica las condiciones en las que ha sido desarrollado son las siguientes:
1. Solo se podrán usar los lenguajes HTML5, CSS3, PHP, y MySQL. Se permite JavaScript únicamente en lo estrictamente necesario.
1. No se usará servidor de chat. Toda la arquitectura debe realizarse mediante querys MySQL, usando MySQLi.
1. Todo lo que se pueda hacer en el chat debe hacerse en la misma ventana (para simplificar el proyecto, ya que hay otro proyecto para el portafolio que consiste en un CMS)
1. No se pueden usar iframes, la misma página de chat debe controlar el flujo.
1. Debe contener ciertos elementos de optimización automática de base de datos.

La idea tras estas normas era que fuera un proceso verdaderamente desafiante del que pudiera aprender ciertos apectos del PHP más puro.

## Sistema de Usuarios

Existe tanto un [login](https://chatv1.josesantiago.es/login.php) como un [registro de usuarios](https://chatv1.josesantiago.es/register.php).

Aunque de algunos de los campos de la base de datos hablaremos más adelante (*last_chat_refresh, current_room, color, o emoji* cuando hablemos del chat, *can_move* cuando hablemos de rangos y *can_change_username* cuando hablemos del perfil). Vamos a explicar algunos de los aspectos más importantes de este sistema de usuarios:

- *id* es la clave primaria de identificación de cada usuario.
- *username, mail* y *password* corresponden a los datos de acceso e identificación.
- *role* y *guard* son campos que indican el rango de la persona y si está o no de guardia. Ver [lista de conectados](#lista-de-conectados) y [rangos](#rangos).
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
- *date* indica la fecha en la que se envía el mensaje utilizando *time()*
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

En el código anterior puedes comprobar que se muestran solo los mensajes que se han publicado después del *time()* indicado en *last_chat_refresh* que es un campo en la tabla del usuario que indica, cuándo fue la última vez que se refrescó el chat. Ante eso es lógico preguntarse ¿Cuándo se actualiza el chat?

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

Si no existe una variable *view* del método *GET* (al abrir el perfil creamos en GET un view=profile, por ejemplo) y si tampoco existe la variable *return* del método *GET* (por ejemplo al pulsar el botón de volver en el perfil se crea en GET un return=), no se actualizaría el *last_chat_refresh*. En cualquier otro caso debe actualizarse. Esto funcionó bastante bien, ya que, a partir de ahora, solo se actualizaba el *last_chat_refresh* en el momento de entrada al chat, al entrar a una sala diferente o al ser movido de sala por un guardia. 

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

Cuando se selecciona una sala en el Lobby se creaa una variable *GET* llamada *goroom*. Se comprueba que la sala exista y que haya permiso para entrar en ella. Si hay permiso se mueve a la sala, si no, se vuelve al Lobby y se muestra el motivo por el que no se puede acceder a la sala (gracias a la función *AllowedInRoom()* que hemos visto en el [sistema de usuarios](#sistema-de-usuarios))

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

Normalmente una persona puede moverse pulsando el botón de "Ir al Lobby", aunque, en las ocasiones en las que un guardia utiliza el comando */capturar* para mover a un usuario, éste perderá la capacidad de moverse de sala hasta que se le vuelva a mover con el comando */llevar*, esto lo veremos mejor en la sección de [comandos](#comandos).

```PHP
if (getUserInfo($_SESSION["userid"])['can_move'] == 1) {
    echo '<p><input type="submit" name="lobby" value="Ir al Lobby" class="menu_button" /></p>';
} else {
    echo '<p><input type="submit" name="lobby" value="Ir al Lobby" class="menu_button" disabled /></p>';
}
```

Los guardias, como veremos de forma más específica en la sección de [rangos](#rangos) y [comandos](#comandos) tienen varios poderes para manejar dónde están los usuarios: en concreto los comandos */capturar* y */llevar*. Estos comandos lo que hacen es cambiar en la base de datos el campo *current_room* de alguien concreto en la tabla de usuarios.

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

Para las alertas, existe el comando */alertar* que básicamente permite a los guardias añadir un *botPrivateMsg* (hablaremos más en profundidad de los comandos en [comandos](#comandos))

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
