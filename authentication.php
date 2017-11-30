<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/

require_once('model/db_connection.php');

class Authentication 
{
        private $db = Null;

        /*
        * params:
        *   $db: DBConnect() object
        */
        function __construct($db)
        {
            session_start();
            $this->db = $db;
        }

        function login($username, $password)
        {
            $select = array('*'); 
            $where  = array('username' => $username,
                            'password' => $password);
    
            return $this->db->select('player', $select, $where);
        }
    
        function signup($first_name, $last_name, $username, $password)
        {
            $row    = array(
                'first_name' => $first_name,
                'last_name'  => $last_name ,
                'username'   => $username  ,
                'password'   => $password  ,
                'time'       => time()     ,
                'admin'      => 0);
                
            return $this->db->insert('player', $row);
        }

        function update_profile($user_id, $first_name, $last_name, $username, $password)
        {
            $set   = array(
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'username'   => $username,
                'password'   => $password
            );
            $where = array(
                'id' => $user_id
            );
            return $this->db->update('player', $set, $where);
        }

        function is_admin($user_id)
        {
            $where = array(
                'id'    => $user_id, 
                'admin' => 1
            );
            return $this->db->select('player', array('*'), $where);
        }

        function reset_password($current_user_id, $user_id, $password)
        {
            if ( $this->is_admin($current_user_id) || 
                 ($current_user_id == $user_id)      )
            {
                $set   = array(
                    'password'   => $password
                );
                $where = array(
                    'id'        => $user_id
                );
                return $this->db->update('player', $set, $where);
            }
            return False;
        }

        function logout()
        {
            session_destroy();
        }
    
        function redirect($url)
        {
            header("Location: $url");
        }
    
       
}

 
?>
