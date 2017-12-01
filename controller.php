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

$conn   = new DBConnection('sqlite:db/hearts.db');
$player = new Player($conn);
$game   = new Game($conn);

$output = array(
    'return' => True,
    'data'   => null,
    'message'=> ''
);


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

?>