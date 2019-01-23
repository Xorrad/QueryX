<?php
//Include the file which contains the function.
include('reqx.php');

$bdd = new PDO("mysql:host=yourhost.com;dbname=yourdatabasename", 'yourusername', 'yourpassword');

$req = req_comp("SELECT * FROM users WHERE Id = ? AND Username = ?", array(1, "Xorrad"));
echo 'Row Count: '.$req[1].'<br>';
echo 'Is Premium: '.$req[0]['Premium'].'<br>';

//ENABLE DEBUG MOD
//an error message will appear because the "userse" table does not exist.
$req = req_comp("SELECT * FROM userse WHERE Id = ?", array(1), true);
?>