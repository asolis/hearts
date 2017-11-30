<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/

require_once('model/player.php');

class Authentication 
{
        private $db     = null;
        private $auth   = null;

        /*
        * params:
        *   $db: DBConnect() object
        */
        function __construct($db)
        {
            session_start();
            $this->db     = $db;
            $this->player   = new Player($db);

            $this->__actions__();
        }

        function __actions__()
        {
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
                    print json_encode($this->actionLogin($_POST['username'], 
                                                   $_POST['password']));
                }
                /**
                 * Logout Action
                 */
                if ( $_POST['action'] == 'logout' )
                {
                    print json_encode($this->actionLogout());
                }  
                
                /**
                 * Signup Account. 
                 * requires:
                 *      first_name,
                 *      last_name,
                 *      username,
                 *      password,
                 */
                if ($_POST['action'] == 'signup'  && 
                    !empty($_POST['first_name'])  && 
                    !empty($_POST['last_name'])   && 
                    !empty($_POST['username'])    && 
                    !empty($_POST['password'])       )
                {
                    print json_encode($this->actionNewUser($_POST['first_name'],
                                                    $_POST['last_name'],
                                                    $_POST['username'],
                                                    $_POST['password']));
            
                }

                /**
                 * Update Profile Account. 
                 * requires:
                 *      first_name,
                 *      last_name,
                 *      username,
                 *      password,
                 */
                if ($_POST['action'] == 'update'  && 
                    !empty($_POST['first_name'])  && 
                    !empty($_POST['last_name'])   && 
                    !empty($_POST['username'])    && 
                    !empty($_POST['password'])       )
                {
                    print json_encode($this->actionUpdateProfile($_SESSION['id'],
                                           $_POST['first_name'],
                                           $_POST['last_name'],
                                           $_POST['username'],
                                           $_POST['password']));
            
                }
            }
        }

        function actionUpdateProfile($user_id, $first_name, $last_name, $username, $password)
        {
            $output = array(
                'return' => False,
                'data'   => [],
                'message'=> ''
            );
            $return  = $this->player->updateProfile($user_id, 
                                                $first_name, 
                                                $last_name, 
                                                $username, 
                                                $password);
            $output['return'] = boolval($return);

            if (!$output['return'])
            {
                $output['message'] = 'username already taken';
            }
            return $output;
        }

        function actionNewUser($first_name, $last_name, $username, $password)
        {
            $output = array(
                'return' => False,
                'data'   => [],
                'message'=> ''
            );
            $return = $this->player->create($first_name, 
                                          $last_name, 
                                          $username, 
                                          $password);

            $output['return'] = boolval($return);

            if (!$output['return'])
            {
                $output['message'] = 'username already taken';
            }
            return $output;
        }

        function actionLogin($username, $password)
        {
            $output = array(
                'return' => False,
                'data'   => [],
                'message'=> ''
            );

            $return  = $auth->valid($username, $password);
            
            $output['return'] = boolval($return);
            
            if ($output['return'])
            {
                $_SESSION['authenticated'] = TRUE;
                $_SESSION = array_merge($_SESSION, $return[0]);
            }
            else 
            {
                $output['message'] = 'Incorrect username and password';
            }
            return $output;
        }

        function actionLogout()
        {
            $output = array(
                'return' => True,
                'data'   => [],
                'message'=> ''
            );
            session_destroy();
            return $output;
        }

        function isUserAuthenticated()
        {
            return !empty($_SESSION["authenticated"]);
        }

        function redirect($url)
        {
            header("Location: $url");
        }
           
}

 $db   = new DBConnect();
 $auth = new Authentication($db);
?>
