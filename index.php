<?php
session_start();

if(isset($_POST['host']) and isset($_POST['database']) and isset($_POST['user']) and isset($_POST['password'])){
    $_SESSION['dados'] = $_POST;
    
    header("Location: select_table.php");
}

require_once 'html_ini.php';
?>
<h2>Enter the connection information for Mysql Server DB</h2>
<form method="post">
    Host:<br/>
    <input name="host" type="text"/>
    Data Base Name:
    <input name="database" type="text"/>
    User :
    <input name="user" type="text"/>
    
    Password:
    <input name="password" type="password"/>
    <br/>
    <input type="submit" value ="Next >" style="width: 99%"/>
</form>

<?php
require_once 'html_fim.php';
?>