<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/
require_once('model/db_connection.php');
require_once('model/player.php');

class Authentication 
{
        private $db       = null;
        private $player   = null;
        private $login_url= null;
        /*
        * params:
        *   $db: DBConnect() object
        */
        function __construct($db)
        {
            session_start();
            $this->db     = $db;
            $this->player   = new Player($db);
            $this->login_url= 'index.html';
            
            if ( !empty($_POST['action'])) 
            {
                /**
                 * Login Action. 
                 * requieres:
                 *       username
                 *       password
                 */
                if ( $_POST['action'] == 'login' && 
                     !empty($_POST['username'])  && 
                     !empty($_POST['password'])     )
                {
                    
                    $user_data = $this->player->valid($_POST['username'], $_POST['password']);
                    if ($user_data)
                    {
                        $_SESSION['authenticated'] = TRUE;
                        $_SESSION = array_merge($_SESSION, $user_data[0]);
                    }
                    else
                    {
                        $this->redirect($this->login_url);
                    }
                }
                /**
                 * Logout Action
                 */
                if ( $_POST['action'] == 'logout' )
                {
                    session_destroy();
                    $this->redirect($this->login_url);
                }  
            }
            if ( !empty($_GET['action']) &&  $_GET['action'] == 'logout' )
            {
                session_destroy();
                $this->redirect($this->login_url);
            } 
    
            if (empty($_SESSION["authenticated"]))
                $this->redirect($this->login_url);

        }
        // function __actions__()
        // {
        //     if ( !empty($_POST['action'])) 
        //     {
        //         /**
        //          * Login Action. 
        //          * requieres:
        //          *       username
        //          *       password
        //          */
        //         if ( $_POST['action'] == 'login' && 
        //              !empty($_POST['username'])  && 
        //              !empty($_POST['password'])     )
        //         {
        //             $return  = $auth->valid($_POST['username'], $_POST['password']);
                    
                    
        //             if ($return)
        //             {
        //                 $_SESSION['authenticated'] = TRUE;
        //                 $_SESSION = array_merge($_SESSION, $return[0]);
        //             }
        //             else
        //             {
        //                 $this->redirect('index.html');
        //             }
        //         }
        //         /**
        //          * Logout Action
        //          */
        //         if ( $_POST['action'] == 'logout' )
        //         {
        //             session_destroy();
        //             $this->redirect('index.html');
        //         }  
               

        //         /**
        //          * Update Profile Account. 
        //          * requires:
        //          *      first_name,
        //          *      last_name,
        //          *      username,
        //          *      password,
        //          */
        //         if ($_POST['action'] == 'update'  && 
        //             !empty($_POST['first_name'])  && 
        //             !empty($_POST['last_name'])   && 
        //             !empty($_POST['username'])    && 
        //             !empty($_POST['password'])       )
        //         {
        //             print json_encode($this->actionUpdateProfile($_SESSION['id'],
        //                                    $_POST['first_name'],
        //                                    $_POST['last_name'],
        //                                    $_POST['username'],
        //                                    $_POST['password']));
            
        //         }

        //         if (empty($_SESSION["authenticated"]))
        //             $this->redirect('index.html');

        //     }
        // }

        // function actionUpdateProfile($user_id, $first_name, $last_name, $username, $password)
        // {
        //     $output = array(
        //         'return' => False,
        //         'data'   => [],
        //         'message'=> ''
        //     );
        //     $return  = $this->player->updateProfile($user_id, 
        //                                         $first_name, 
        //                                         $last_name, 
        //                                         $username, 
        //                                         $password);
        //     $output['return'] = boolval($return);

        //     if (!$output['return'])
        //     {
        //         $output['message'] = 'username already taken';
        //     }
        //     return $output;
        // }

        function redirect($url)
        {
            header("Location: $url");
        }
           
}

 $conn   = new DBConnection('sqlite:db/hearts.db');
 $auth   = new Authentication($conn);
?>
