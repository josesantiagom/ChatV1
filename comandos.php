<?php
/*  ***********************************************************************************
 * CHAT V1 - ESTILO LYCOS CHAT, COMPLETAMENTE EN PHP + AJAX + JS                      *
 * Proyecto para el PORTAFOLIO                                                        *
 *                                                                                    *
 * Author: Jose Santiago Muñoz                                                        *
 * Inicio del proyecto: agosto 2023, final del proyecto:                              *      
 * *********************************************************************************  */


/*------------------------------------------------------------------------------------*
-                                 COMANDOS DE CHAT                                    *
--------------------------------------------------------------------------------------*/

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

//FUNCIÓN DE PASO PARA COMPROBAR DAR PASO AL COMANDO
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

// FUNCIÓN QUE EJECUTA EL COMANDO ELEGIDO
function doExecuteCommand($arrg) {
    switch ($arrg[0]) {
        case '/guardia':
            if (!isInGuard($_SESSION["username"])) {
                $query = $GLOBALS['db']->query("UPDATE `users` SET guard = '1' WHERE id = '".$_SESSION["userid"]."'");
                botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Ahora estás de guardia. Recuerda que tu labor como guardia es la de ayudar a que todas las personas resuelvan sus dudas, e incluso resolver incidencias o mantener el orden en las salas.",$_SESSION["username"]);
            } else {
                $query = $GLOBALS['db']->query("UPDATE `users` SET guard = '0' WHERE id = '".$_SESSION["userid"]."'");
                botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Has dejado de estar de guardia ¡Pásatelo bien en el chat!",$_SESSION["username"]);
            }
            break;
        case '/quien':
            $newbie = @isNewbie(getUserInfo(getUserId($arrg[1]))['id]']);
            $guardia = @isGuard(getUserInfo(getUserId($arrg[1]))['id]']);

            if (!isset($arrg[1]) or $arrg[1] == "") {
                botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Si quieres saber algo sobre alguien tienes que indicarme su nombre mediante /quien {nick} cambiando {nick} por el nombre de quien busques.",$_SESSION["username"]);
            } elseif ($newbie and $guardia) {
                botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! ¿Quieres saber sobre ".$arrg[1]."? Es un guardia novato. Lo vi por última vez el ".date("d/m/Y",@getUserInfo(getUserId($arrg[1]))['last_online'])." a las ".date("H:i",getUserInfo(getUserId($arrg[1]))['last_online']).".",$_SESSION["username"]);
            } elseif ($newbie and !$guardia) {
                botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! ¿Quieres saber sobre ".$arrg[1]."? Es un nuevo viajero. Lo vi por última vez el ".date("d/m/Y",@getUserInfo(getUserId($arrg[1]))['last_online'])." a las ".date("H:i",getUserInfo(getUserId($arrg[1]))['last_online']).".",$_SESSION["username"]);
            } elseif (!$newbie and $guardia) {
                botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! ¿Quieres saber sobre ".$arrg[1]."? Es un miembro de la guardia. Lo vi por última vez el ".date("d/m/Y",@getUserInfo(getUserId($arrg[1]))['last_online'])." a las ".date("H:i",getUserInfo(getUserId($arrg[1]))['last_online']).".",$_SESSION["username"]);
            } else {
                botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! ¿Quieres saber sobre ".$arrg[1]."? Es un viejo conocido de esta ciudad. Lo vi por última vez el ".date("d/m/Y",@getUserInfo(getUserId($arrg[1]))['last_online'])." a las ".date("H:i",getUserInfo(getUserId($arrg[1]))['last_online']).".",$_SESSION["username"]);
            }
            break;
         case '/rango':
            botPrivateMsg(4,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Tu rango es ".$_SESSION["role"].".",$_SESSION["username"]);
            break;
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
         case '/info':
            if (!isset($arrg[1]) or $arrg[1] == "") {
                botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Tienes qué decirme de quién quieres información, el comando es /info {nick}.",$_SESSION["username"]);
            } else {
                if (userExists($arrg[1])) {
                    if (userIsOnline($arrg[1])) {
                        botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! ".$arrg[1]." existe y está conectado.",$_SESSION["username"]);
                    } else {
                        botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! ".$arrg[1]." existe pero no está conectado.",$_SESSION["username"]);
                    }
                } else {
                    botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! ".$arrg[1]." no existe.",$_SESSION["username"]);
                }
            }
            break;
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
            break;
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
        case '/seguir':
            if (!isset($arrg[1]) or $arrg[1] == "") {
                botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Tienes qué decirme a quién quieres seguir con /seguir {nick}.",$_SESSION["username"]);
            } else {
                if (userExists($arrg[1])) {
                    if (userIsOnline($arrg[1])) {
                        $userinfo = getUserInfo(getUserId($arrg[1]));

                        if (AllowedInRoom($_SESSION['userid'], $userinfo['current_room']) == 'ok') {
                            $sql = $GLOBALS['db']->query("UPDATE `users` SET current_room = '".$userinfo['current_room']."' WHERE id = '".$_SESSION["userid"]."'");
                        } else {
                            $error = AllowedInRoom($_SESSION['userid'], $userinfo['current_room']);
                            botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! No puedes seguir a ".$arrg[1].". ".showChatError($error).".",$_SESSION["username"]);
                        }

                    } else {
                        botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! No puedes seguir a ".$arrg[1]." porque no está conectado actualmente.",$_SESSION["username"]);
                    }
                } else {
                    botPrivateMsg(5,getRoomInfo($_SESSION["chatroom"])['id'], '¡Hola '.$_SESSION["username"]."! Estás intentando seguir a alguien que no existe. Comprueba el nick que has escrito.",$_SESSION["username"]);
                }
            }
            break;
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
        default:
            break;
    }
}

//Función para informar de que un comando es incorrecto
function incorrectCommand() {
    $sql = "INSERT INTO `chat` (uid, rid, msg, conditions, date, destiny) VALUES(3, '".getRoomInfo($_SESSION["chatroom"])['id']."', 'Lo siento, ".$_SESSION["username"]." pero el comando que estás intentando utilizar no es correcto, quizás debas probar otra cosa.','private_bot','".time()."', '".$_SESSION["username"]."')"; 
    $query = $GLOBALS['db']->query($sql) or die($GLOBALS['db']->error);
}

?>