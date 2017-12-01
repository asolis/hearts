<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once('model/db_connection.php');
require_once('model/player.php');
require_once('model/game.php');

session_start();

$conn   = new DBConnection('sqlite:db/hearts.db');
$player = new Player($conn);
$game   = new Game($conn);

$output = array(
    'return' => True,
    'data'   => null,
    'message'=> ''
);

/**
 * Show games stats:
 * requires 
 *      action
 * optional
 *      column
 */
if ( !empty($_GET['action']) && $_GET['action'] == 'stats') 
{
    if (!empty($_GET['column']))
    {
        $data = $game->getStats($_GET['column'], SORT_DESC);
    }
    else 
    {
        $data = $game->getStats();
    }
    $output['data'] = $data;

    print json_encode($output);
}
/**
 * Show game by id
 * requires
 *      action 
 *      game_id
 */
else if ( !empty($_GET['action']) && $_GET['action'] == 'game') 
{
    $scores = array(
        'player1_score' => 0,
        'player2_score' => 0,
        'player3_score' => 0,
        'player4_score' => 0
    );

    
    $hands = $game->getHands($_GET['game_id']);

    $hands = array_map(function($index, $value){ 
                            return array_merge(array('index'=> $index + 1), $value);
                       }, array_keys($hands), $hands);
    
    foreach ($hands as $index => $hand)
        foreach($scores as $key => $value)
            $scores[$key] += $hand[$key];

    $scores['index'] = 'T';
            
    $game = array(
        'game_id' => $_GET['game_id'],
        'hands'   => $hands,
        'scores'  => $scores
    );
    
    $output['data'] =  $game;

    print json_encode($output);
}
/**
 * Signup Account. (POST)
 * requires:
 *      action
 *      first_name,
 *      last_name,
 *      username,
 *      password,
 */
else if (!empty($_POST['action']) && 
    !empty($_POST['first_name'])  && 
    !empty($_POST['last_name'])   && 
    !empty($_POST['username'])    && 
    !empty($_POST['password'])    &&
    $_POST['action'] == 'register')
{
    
   
    $return = $player->create($_POST['first_name'], 
                              $_POST['last_name'], 
                              $_POST['username'], 
                              $_POST['password']);
    
    $output['return'] = boolval($return);

    
    if (!$output['return'])
        $output['message'] = 'Username taken';
    print json_encode($output);
}


else if (!empty($_POST['action']) && $_POST['action'] == 'profile')
{
    
    $output['return']   = $_SESSION['authenticated'];
    $output['data']     = array(
        'first_name' => $_SESSION['first_name'],
        'last_name'  => $_SESSION['last_name'],
        'username'   => $_SESSION['username']
    );
    print json_encode($output);
}


else if (!empty($_POST['action']) && $_POST['action'] == 'update_profile')
{
    
    
    
    $return = $player->updateProfile($_SESSION['id'], 
                            $_POST['first_name'], 
                            $_POST['last_name'], 
                            $_POST['username'], 
                            $_POST['password']);
    $output['return']   = boolval($return);
    if ($output['return'])
    {
        $_SESSION['first_name'] = $_POST['first_name'];
        $_SESSION['last_name']  = $_POST['last_name'];
        $_SESSION['username']   = $_POST['username'];
    }
    else {
        $output['message'] = array("username" => "Username taken"); 
    }
    print json_encode($output);
}
?>