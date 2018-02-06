<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/

require_once('db_connection.php');

class Player 
{
        private $db = null;

        /*
        * params:
        *   $db: DBConnect() object
        */
        function __construct($db)
        {
            $this->db = $db;
        }

        function valid($username, $password)
        {
            $select = array('id','username','first_name','last_name', 'admin', 'time'); 
            $where  = array('username' => $username,
                            'password' => $password);
    
            return $this->db->select('player', $select, $where);
        }
    
        function create($first_name, $last_name, $username, $password)
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

        function updateProfile($user_id, $first_name, $last_name, $username, $password)
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

        function isAdmin($user_id)
        {
            $where = array(
                'id'    => $user_id, 
                'admin' => 1
            );
            return $this->db->select('player', array('*'), $where);
        }

        function listUsers()
        {
            return $this->db->select('player', array('id','username', 'first_name', 'last_name'));
        }
        function listPlayers()
        {
            return $this->db->select('player', array('id','username', 'first_name', 'last_name'), array('admin' => 0));
        }

        function resetPassword($current_user_id, $user_id, $password)
        {
            if ( $this->isAdmin($current_user_id) || 
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
}

 
?>
