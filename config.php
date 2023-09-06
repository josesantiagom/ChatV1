<?php

/*  ***********************************************************************************
 * CHAT V1 - ESTILO LYCOS CHAT, COMPLETAMENTE EN PHP + AJAX + JS                      *
 * Proyecto para el PORTAFOLIO                                                        *
 *                                                                                    *
 * Author: Jose Santiago Muñoz                                                        *
 * Inicio del proyecto: agosto 2023, final del proyecto:                              *      
 * *********************************************************************************  */


/*------------------------------------------------------------------------------------*
-                              ACCIONES AUTOMATIZADAS                                 *
--------------------------------------------------------------------------------------*/

// CONEXIÓN A LA BASE DE DATOS
$dbHost = ""; //Databse host
$dbUser = ""; //Database access user
$dbPass = ""; //Database access password
$dbMainDb = ""; //Main database name

if (empty($dbHost) or empty($dbUser) or empty($dbPass) or empty($dbMainDb)) {
    die("Tienes que configurar los datos de tu base de datos en config.php");
} else {
    $db = new mysqli($dbHost, $dbUser, $dbPass, $dbMainDb) or die($db->error);
}

// OPTIMIZACIÓN ATOMÁTICA DE LA BASE DE DATOS
$actualTime = time();
$last_optimize = getConfig('int', 'last_optimize');
$next_optimize = $last_optimize + 604800;

if ($actualTime >= $next_optimize) {
    $sql = "OPTIMIZE TABLE `config`, `users`, `logs`";
    $db->query($sql) or die($db->error);
    $sql2 = "UPDATE `config` SET int_content = '".$actualTime."' WHERE name = 'last_optimize'";
    $db->query($sql2) or die($db->error);
    addInLog("Root", "Se ha optimizado automáticamente la base de datos");
}

//Borrado de los mensajes private_bot que pasaran el tiempo estimado en la base de datos
$seconds = getConfig('int','private_botmsg_del');
$query = $db->query("DELETE FROM `chat` WHERE conditions = 'private_bot' and date <= '".time()-$seconds."'");


/*------------------------------------------------------------------------------------*
-                                    FUNCIONES                                        *
--------------------------------------------------------------------------------------*/

// FUNCIÓN PARA DEPURAR ERRORES EN LA VISTA
function mostrarErrores() {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

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

//FUNCIÓN PARA COMPROBAR SI UNA SALA EXISTE
function roomExists($shortname) {
    $sql = "SELECT count(id) FROM `rooms` WHERE shortname = '".$shortname."'";
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

//FUNCIÓN PARA ACTUALIZAR EL CAMPO LASTONLINE
function updateLastOnline($username, $time) {

    if ($username == "_bots") {
        $sql = "UPDATE `users` SET last_online = '".$time."' WHERE bot = '1'";  
    } else {
        $sql = "UPDATE `users` SET last_online = '".$time."' WHERE username = '".$username."'";  
    }
        $GLOBALS['db']->query($sql);
        return true;
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

//FUNCIÓN PARA COMPROBAR SI UN USARIO ESTÁ DE GUARDIA
function isInGuard($username) {
    $sql = "SELECT guard FROM `users` WHERE username= '".$username."'";
    $query = $GLOBALS['db']->query($sql);
    $resp = $query->fetch_array();

    if ($resp[0] == 1) {
        return true;
    } else {
        return false;
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

//FUNCIÓN PARA COMPROBAR SI UN SUUARIO ES GENERAL
function isGeneral($userid) {
    $sql = "SELECT role FROM `users` WHERE id = '".$userid."'";
    $query = $GLOBALS['db']->query($sql);
    $resp = $query->fetch_array();

    if ($resp['role'] > 4) {
        return true;
    } else {
        return false;
    }
}

//FUNCIÓN PARA OBTENER LOS DATOS DE UNA SALA
function getRoomInfo($room) {
    $sql = "SELECT * FROM `rooms` WHERE shortname = '".$room."'";
    $query = $GLOBALS['db']->query($sql);
    $roominfo = $query->fetch_array();
    $return = array (
        'id' => $roominfo['id'],
        'name' => $roominfo['name'],
        'shortname' => $roominfo['shortname'],
        'conditions' => $roominfo['conditions']
    );

    return $return;
}

//FUNCIÓN QUE REGISTRA UN PRIVATE BOT MSG EN UNA SALA CONCRETA
function botPrivateMsg($botid, $rid, $msg, $destiny) {
    $sql = "INSERT INTO `chat` (uid, rid, msg, conditions, date, destiny) VALUES('".$botid."', '".$rid."', '".$msg."','private_bot','".time()."', '".$destiny."')"; 
    $query = $GLOBALS['db']->query($sql) or die($GLOBALS['db']->error);
    return true;
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

// FUNCIÓN PARA OBTENER UN MENSAJE DE ERROR
function showChatError($error) {
    $errors = array(
        '001x001' => "El nombre de usuario no está disponible", 
        '001x002' => 'Ya hay una cuenta registrada con ese email',
        '001x003' => 'Sólo puedes usar letras, números, puntos, guiones y barras bajas en el nick.',
        '001x004' => 'El nombre de usuario es muy corto, tiene que tener, al menos, 3 carácteres',
        '001x005' => 'El email introdocido no es válido.',
        '002x001' => 'El nombre de usuario es incorrecto',
        '002x002' => 'La contraseña no es correcta',
        '003x001' => 'Solo la guardia tiene permiso para entrar a esa sala',
        '003x002' => 'Esta sala solo es para novatos',
        '003x003' => 'Esta sala es privada y no tienes permiso para entrar',
        '003x004' => 'Solo los capitanes tienen permiso para entrar a esta sala',
        '004x001' => 'No se puede cambiar la contraseña, ya que las dos contraseñas introducidas no coinciden',
        '004x002' => 'Ya existe una persona con ese nombre de usuario, prueba otro'
    );

    if (isset($errors[$error])) {
        return $errors[$error];
    } else {
        return false;
    }
}

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

// FUNCIÓN QUE AÑADE UNA ENTRADA AL LOG DE LA BASE DE DATOS
function addInLog($who, $action) {
    $date = time();
    $ip = $_SERVER['REMOTE_ADDR'];
    $sql = "INSERT INTO `logs` (date, ip, who, action) VALUES('".$date."', '".$ip."', '".$who."', '".$action."')";

    $inserting = $GLOBALS['db']->query($sql) or die($GLOBALS['db']->error);
}

?>