<?php

//Include the classes
require 'reqx.php';

//Initialize the database object
$database = new Database();

//Authenticate to the database using credentials
$database->connect('localhost', 'db', 'root', '');

//Check if the connection has succeeded
if($database->is_connected())
{
    //Execute a sql query to the database
    $query = $database->execute('SELECT * FROM users WHERE Username = ?', array('Xorrad'));
    //$query = $database->execute('UPDATE users SET Username = ? WHERE Id = ?', array('Xorrad2', 1));

    //Check if the query has succeeded
    if($query->has_succeeded())
    {
        var_dump($query->get_rows());
        echo $query->get_count().' rows has been retrieve from the database';
    }
    else
    {
        //Print the error
        print_r($database->get_last_error()->getMessage());
    }
}
else
{
    //Print the error
    print_r($database->get_last_error()->getMessage());
}

?>
