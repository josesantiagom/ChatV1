<?php
$userinfo = getUserInfo($_SESSION["userid"]);

if (isset($_POST["change_chat_options"])) {
    $color = $_POST["color"];
    $emoji = $_POST["emoji"];

    $sql = "UPDATE `users` SET color = '".$color."', emoji = '".$emoji."' WHERE id = '".$_SESSION["userid"]."'";
    $query = $db->query($sql);

    $alert = "Se han realizado los cambios correctamente";
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
    <h1>Cambiar opciones del chat</h1><br>

    <table>
    <form name="change_chat_options" method="post" action="">
        <tr>
        <td>Color del nombre&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br></td>
        <td>
            <select name="color">
                <option value="A93226"><font color="#A93226">Rojo oscuro</font></options>
                <option value="E74C3C"><font color="#E74C3C">Rojo claro</font></options>
                <option value="9B59B6"><font color="#9B59B6">Violeta</font></options>
                <option value="5B2C6F"><font color="#5B2C6F">Morado</font></options>
                <option value="2471A3"><font color="#2471A3">Azul</font></options>
                <option value="5DADE2"><font color="#5DADE2">Celeste</font></options>
                <option value="76D7C4"><font color="#76D7C4">Aguamarina</font></options>
                <option value="196F3D"><font color="#196F3D">Verde oscuro</font></options>
                <option value="28B463"><font color="#28B463">Verde claro</font></options>
                <option value="B7950B"><font color="#B7950B">Bronce</font></options>
                <option value="F1C40F"><font color="#F1C40F">Amarillo</font></options>
                <option value="F39C12"><font color="#F39C12">Naranja</font></options>
                <option value="6E2C00"><font color="#6E2C00">Marrón</font></options>
            </select><br>
        </td>
        </tr>
        <tr>
            <td valign="top">Emoji</td>
            <td>
                <table>
                    <tr>   
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😄">
                            <label for="😄">😄</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😁">
                            <label for="😁">😁</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😂">
                            <label for="😂">😂</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😃">
                            <label for="😃">😃</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤣">
                            <label for="🤣">🤣</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😀">
                            <label for="😀">😀</label><br>
                        </td> 
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😅">
                            <label for="😅">😅</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😆">
                            <label for="😆">😆</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😉">
                            <label for="😉">😉</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😊">
                            <label for="😊">😊</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😋">
                            <label for="😋">😋</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😎">
                            <label for="😎">😎</label><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😍">
                            <label for="😍">😍</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😘">
                            <label for="😘">😘</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🥰">
                            <label for="🥰">🥰</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😗">
                            <label for="😗">😗</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😙">
                            <label for="😙">😙</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🥲">
                            <label for="🥲">🥲</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😚">
                            <label for="😚">😚</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🙂">
                            <label for="🙂">🙂</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤗">
                            <label for="🤗">🤗</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤩">
                            <label for="🤩">🤩</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤔">
                            <label for="🤔">🤔</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🫡">
                            <label for="🫡">🫡</label><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤨">
                            <label for="🤨">🤨</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😐">
                            <label for="😐">😐</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😑">
                            <label for="😑">😑</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😶">
                            <label for="😶">😶</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🫥">
                            <label for="🫥">🫥</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😶‍🌫️">
                            <label for="😶‍🌫️">😶‍🌫️</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🙄">
                            <label for="🙄">🙄</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😏">
                            <label for="😏">😏</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😣">
                            <label for="😣">😣</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤐">
                            <label for="🤐">🤐</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😪">
                            <label for="😪">😪</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😫">
                            <label for="😫">😫</label><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🥱">
                            <label for="🥱">🥱</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😴">
                            <label for="😴">😴</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😜">
                            <label for="😜">😜</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤤">
                            <label for="🤤">🤤</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😒">
                            <label for="😒">😒</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🫠">
                            <label for="🫠">🫠</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤑">
                            <label for="🤑">🤑</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😲">
                            <label for="😲">😲</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😖">
                            <label for="😖">😖</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😤">
                            <label for="😤">😤</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😭">
                            <label for="😭">😭</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😨">
                            <label for="😨">😨</label><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤯">
                            <label for="🤯">🤯</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😮‍💨">
                            <label for="😮‍💨">😮‍💨</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😱">
                            <label for="😱">😱</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🥵">
                            <label for="🥵">🥵</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🥶">
                            <label for="🥶">🥶</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😳">
                            <label for="😳">😳</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😵">
                            <label for="😵">😵</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😵‍💫">
                            <label for="😵‍💫">😵‍💫</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🥴">
                            <label for="🥴">🥴</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😡">
                            <label for="😡">😡</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤬">
                            <label for="🤬">🤬</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😷">
                            <label for="😷">😷</label><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤒">
                            <label for="🤒">🤒</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤕">
                            <label for="🤕">🤕</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤢">
                            <label for="🤢">🤢</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤮">
                            <label for="🤮">🤮</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😇">
                            <label for="😇">😇</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🥸">
                            <label for="🥸">🥸</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤠">
                            <label for="🤠">🤠</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤡">
                            <label for="🤡">🤡</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤥">
                            <label for="🤥">🤥</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🫢">
                            <label for="🫢">🫢</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤓">
                            <label for="🤓">🤓</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="😈">
                            <label for="😈">😈</label><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="👹">
                            <label for="👹">👹</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="💀">
                            <label for="💀">💀</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="👻">
                            <label for="👻">👻</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="👽">
                            <label for="👽">👽</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="👾">
                            <label for="👾">👾</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🤖">
                            <label for="🤖">🤖</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="💩">
                            <label for="💩">💩</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🙈">
                            <label for="🙈">🙈</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐶">
                            <label for="🐶">🐶</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐺">
                            <label for="🐺">🐺</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐱">
                            <label for="🐱">🐱</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🦁">
                            <label for="🦁">🦁</label><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐯">
                            <label for="🐯">🐯</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🦊">
                            <label for="🦊">🦊</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🦝">
                            <label for="🦝">🦝</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐮">
                            <label for="🐮">🐮</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐷">
                            <label for="🐷">🐷</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐭">
                            <label for="🐭">🐭</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐰">
                            <label for="🐰">🐰</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐻">
                            <label for="🐻">🐻</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐻‍❄️">
                            <label for="🐻‍❄️">🐻‍❄️</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐨">
                            <label for="🐨">🐨</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐼">
                            <label for="🐼">🐼</label><br>
                        </td>
                        <td>
                            <input type="radio" id="emoji" name="emoji" value="🐸">
                            <label for="🐸">🐸</label><br>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <p><br><input type="submit" name="change_chat_options" value="Cambiar opciones de chat" />
    <br><br>
    </form> 
    <p>El color del nombre de usuario y el emoji aparecen en el chat.
</div>