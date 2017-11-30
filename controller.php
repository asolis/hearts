<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/

require_once('model/player.php');
require_once('model/game.php');

class Controller 
{
        private $db       = null;
        private $player   = null;
        private $game     = null;

        /*
        * params:
        *   $db: DBConnect() object
        */
        function __construct($db)
        {
            $this->db     = $db;
            $this->player   = new Player($db);
            $this->game     = new Game($db);

            
        }
}

?>