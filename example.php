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
    //Execute a sql request to the database
    $request = $database->execute('SELECT * FROM users WHERE Username = ?', array('Xorrad'));
    //$request = $database->execute('UPDATE users SET Username = ? WHERE Id = ?', array('Xorrad2', 1));

    //Check if the request has succeeded
    if($request->has_succeeded())
    {
        var_dump($request->get_rows());
        echo $request->get_count().' rows has been retrieve from the database';
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