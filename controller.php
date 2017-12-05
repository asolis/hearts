<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once('model/db_connection.php');
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

function checkEmpty($variable, $fields, &$messages)
{

    $empty = False;
    foreach($fields as $field)
    {
        if (empty($variable[$field]))
        {
            $messages[$field] = 'Empty';  
            $empty = True;  
        }
    }
    return $empty;
}
/**
 * Login action sets the current session values
 */
if ( !empty($_POST['action']) && $_POST['action'] == 'login')
{
    $messages = array();
    $empty    = checkEmpty($_POST,['username', 'password'], $messages);
    
    if (!$empty)
    {
        $result = $player->valid($_POST['username'], $_POST['password']);
        if ($result)
        {
            $_SESSION['authenticated'] = TRUE;
            $_SESSION['CURRENT_GAME']  = -1;
            $_SESSION = array_merge($_SESSION, $result[0]);
        }
        else
        {
            $messages['username'] = 'Wrong user and password';
            $messages['password'] = '';
        }
    }
    $output['return'] = !$empty && $result;
    $output['data']   = null;
    $output['message']= $messages;
    
    print json_encode($output);
} 
/**
 * logout and destroy session
 */
else if ( !empty($_POST['action']) &&  $_POST['action'] == 'logout' )
{
    session_destroy();
    print json_encode($output);
} 
/**
 * Show games stats:
 * requires 
 *      action
 * optional
 *      column
 */
else if ( !empty($_GET['action']) && $_GET['action'] == 'stats') 
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
    
    $usernames = array_map(function($user){
        return ($user == null) ? '...' : $user;
    },$game->getPlayersUsernames($_GET['game_id']));


    $game = array(
        'game_id' => $_GET['game_id'],
        'hands'   => $hands,
        'players' => $usernames,
        'scores'  => $scores
    );
    
    $output['data'] =  $game;

    print json_encode($output);
}
/**
 * Register Account. (POST)
 * requires:
 *      action
 *      first_name,
 *      last_name,
 *      username,
 *      password,
 */
else if (!empty($_POST['action']) && $_POST['action'] == 'register')
{
   
    $messages = [];
    $empty    = checkEmpty($_POST,['first_name', 'last_name', 
                                'username', 'password'], $messages );
    if (!$empty)
    {
        $result = boolval($player->create($_POST['first_name'], 
                                  $_POST['last_name'], 
                                  $_POST['username'], 
                                  $_POST['password']));
        if (!$result)
                $messages['username'] = 'Username taken';
    }
    
    $output['return'] = !$empty && $result;
    $output['data']   = null;
    $output['message']= $messages;
    print json_encode($output);
}
/*
*   Retrieves profile information from sesssion
*   Requires:
*       action
*/

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

/**
 * Updates the session profile
 * requires
 *      action
 */
else if (!empty($_POST['action']) && $_POST['action'] == 'update_profile')
{
    
    $messages = array();
    $empty    = checkEmpty($_POST,['first_name', 'last_name', 
                                   'username', 'password'], $messages);
    
    if (!$empty)
    {
        $result = boolval($player->updateProfile($_SESSION['id'], 
                                        $_POST['first_name'], 
                                        $_POST['last_name'], 
                                        $_POST['username'], 
                                        $_POST['password']));
        if ($result)
        {
            $_SESSION['first_name'] = $_POST['first_name'];
            $_SESSION['last_name']  = $_POST['last_name'];
            $_SESSION['username']   = $_POST['username'];
        }
        else 
        {
            $messages['username'] = 'Username taken';
        }    
    }

    $output['return'] = !$empty && $result;
    $output['data']   = null;
    $output['message']= $messages;
    
    print json_encode($output);
}
/**
 * creates a new game and sets it as the current_game
 * requires
 *  action
 */
else if (!empty($_POST['action']) && $_POST['action'] == 'create')
{
    $_SESSION['CURRENT_GAME'] = $game->create($_SESSION['id']);
    $output['data'] = $_SESSION['CURRENT_GAME'];
    print json_encode($output);
}
/**
 * finish the current_game
 * requires
 * action
 */
else if (!empty($_POST['action']) && $_POST['action'] == 'finish')
{
    $finished = $game->finish($_SESSION['id'],$_SESSION['CURRENT_GAME']);
    if ($finished)
    {
        $_SESSION['CURRENT_GAME'] = -1;
    }
        
    $output['return'] = $finished;
    print json_encode($output);
}
/**
 * Player joins a game defined by game_id
 * requires
 *  action
 *  game_id
 */
else if (!empty($_POST['action']) && $_POST['action'] == 'join')
{
    $valid = $game->isValidGameCode($_POST['game_id']);
    if (!$valid)
    {
        $output['message'] = 'Not a valid game';
        $output['return'] = False;
    }
    if ($valid)
    {
        $joined = $game->join($_SESSION['id'], $_POST['game_id']);
        if ($joined)
            $_SESSION['CURRENT_GAME'] = $_POST['game_id'];
        else
             $output['message'] = 'Game is full';
        
        $output['return'] = $joined;
        $output['data']   = $_SESSION['CURRENT_GAME'];
    }
    
    
    //send message 
    //Game is full (4 ppl already playing)
    //Game doesn't exist 
    print json_encode($output);
}
/**
 * Add a hand to the current game
 * requieres
 *  action
 *  player1_score
 *  player2_score
 *  player3_score
 *  player4_score
 */
else if (!empty($_POST['action']) && $_POST['action'] == 'add_hand')
{
    $total  = $_POST['player1_score'] +
            $_POST['player2_score'] +
            $_POST['player3_score'] +
            $_POST['player4_score'];

    $valid  = False;
    $result = 0;
    if ($total == 26 || $total == (26 * 3))
    {
        $valid  = True;
        $result = $game->addHand($_SESSION['id'], 
                            $_SESSION['CURRENT_GAME'],
                            $_POST['player1_score'],
                            $_POST['player2_score'],
                            $_POST['player3_score'],
                            $_POST['player4_score']);
    }

    if (!$valid)
    {
        $output['message'] = 'Invalid score';
    }

    $output['return'] = $valid && $result;
    $output['data']   = $result;
    
    print json_encode($output);
}
/**
 * Removes the last hand in current game
 * requieres
 *  action
 */
else if (!empty($_POST['action']) && $_POST['action'] == 'last_hand')
{
    $hands = $game->getHands($_SESSION['CURRENT_GAME']);
    
    if ($hands)
    {
        $last_hand = array_pop($hands);
        $deleted = $game->deleteHand($_SESSION['id'],
                                    $_SESSION['CURRENT_GAME'],
                                    $last_hand['id']);
        

    }
    print json_encode($output);
}
/**
 * requires
 *  action
 *  game_id
 */
else if (!empty($_POST['action']) && $_POST['action'] == 'controls')
{
    $players  = $game->getPlayersUsernames($_SESSION['CURRENT_GAME']);
    $complete = True;
    
    $finished  = boolval($game->isGameFinished($_POST['game_id']) || 
                         $game->isPlaying(null, $_POST['game_id']));
    
    $result  = array_map(function($number, $label, $username)  {
        $username = ($username == null)? '...' : $username;
        return ['label'=> sprintf('%s_score',$label), 'username'=>$username, 'number' => $number]; 
    },[1,2,3,4], array_keys($players),$players);

    $output['return'] = $complete;
    $output['data']   = $result;
    $output['finished'] = $finished; //new value
    print json_encode($output);
}

//ADMINISTRATION TASKS

/**
 * requires
 *  action
 */
else if (!empty($_POST['action']) && $_POST['action'] == 'list_users')
{
    $output['data'] = $player->listUsers();
    
    print json_encode($output);
}


/**
 * requires
 *  action
 *  user_id
 *  password
 */
else if (!empty($_POST['action']) && $_POST['action'] == 'reset_password')
{
    $output['return'] = boolval($player->resetPassword($_SESSION['id'], $_POST['user_id'], $_POST['password']));

    print json_encode($output);
}

/**
 * requires
 *  action
 *  game_id
 */
else if (!empty($_POST['action']) && $_POST['action'] == 'unlock_game')
{
    $output['return'] = boolval($game->unlockGame($_SESSION['id'], $_POST['game_id']));
    
    print json_encode($output);
}


?>