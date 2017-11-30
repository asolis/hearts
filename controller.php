<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/

require_once('authentication.php');

$auth = new Authentication();

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
        $rows = $auth->login($_POST['username'], $_POST['password']);

        if ($rows)
        {
            $_SESSION['authenticated'] = TRUE;
            $_SESSION = array_merge($_SESSION, $rows[0]);
        }
    }
    /**
     * Logout Action
     */
    if ( $_POST['action'] == 'logout' )
    {
        $auth->logout();
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
        $auth->signup($_POST['first_name'],
                      $_POST['last_name'],
                      $_POST['username'],
                      $_POST['password']);

    }
    /**
     * Signup Account. 
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
        $auth->update_profile($_POST['first_name'],
                              $_POST['last_name'],
                              $_POST['username'],
                              $_POST['password']);

    }
    
}

if(empty($_SESSION["authenticated"])) 
{
    $auth->redirect('signup.html');
}






 
?>
