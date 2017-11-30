<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once('db_connection.php');
require_once('game.php');
require_once('player.php');

$db = new DBConnection();

$players = new Player($db);
$game    = new Game($db);

// var_dump($players->createUser('Andres', 'Solis Montero', 'asmmsa', 'hola'));
// var_dump($players->createUser('Andres', 'Solis Montero', 'asmmsa2', 'hola'));
// var_dump($players->createUser('Andres', 'Solis Montero', 'asmmsa3', 'hola'));
 //var_dump($players->createUser('Andres', 'Solis Montero', 'asmamsa4', 'hola'));

//var_dump($game->createGame(20));
var_dump($game->showStats());

?>
