<?php
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
} else {
    updateLastOnline($_SESSION["username"], time());
}

if(isset($_POST["volver"])) {
    echo '<script type="text/JavaScript"> location.assign("chat.php?return="); </script>';
}
?>
<link href="../css/modules.css" rel="stylesheet">
<div class="contain_box">
    <br><div class="module_title">Mi perfil_</div><br>
    <div class="module_column">
        <table name="menu">
            <tr valign="center"><td class="menu_impar" valign="center">&nbsp;&nbsp;<a href="?view=pm&m=inbox">Mensajes recibidos</a></td></tr>
            <tr valign="center"><td class="menu_par" valign="center">&nbsp;&nbsp;<a href="?view=pm&m=sendbox">Mensajes enviados</a></td></tr>
        </table>
    </div>
    <div class="return_text">
    <form name="return" method="post" action="">    
        <button name="volver">⬅️ Volver</button></div>
    </form>
    <div class="module_content">
        <?php
            if (!isset($_GET["m"])) {
                include("pm/inbox.php");
            } else {
                $m = htmlentities($_GET["m"]);

                if (file_exists('pm/'.$m.'.php')) {
                    include('pm/'.$m.'.php');
                } else {
                    echo "<div class='imgcenter'><p>¡Ha ocurrido un error cargando la página!</p><p><img src='img/modulerror.png' width='30% height='30%' /></p></div>";
                }
            }
        ?>
    </div>
</div>