<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/

require_once('db_connection.php');
require_once('player.php');
class Game 
{
        private $db     = null;

        function __construct($db)
        {
            $this->db = $db;
            
        }
        //-----------Private Functions ------------------
        /**
         * insert stats tuple in table
         */
        private function _insert_stat($table, $game_id, $player_id)
        {
            $values = array(
                'game_id'   => $game_id,
                'player_id' => $player_id
            );
            return $this->db->insert($table, $values);
        }
        /**
         * deletes all the stats associated with a game_id in
         * a table
         */
        private function _delete_stat($table, $game_id)
        {
            $values = array(
                'game_id' => $game_id
            );
            return $this->db->delete($table, $values);
        }
        
        /**
         * remove all the stats from a game
         */
        private function _clear_game_stats($game_id)
        {
                $w = $this->_delete_stat('wins',$game_id);
                $l = $this->_delete_stat('losses',$game_id);
                $c = $this->_delete_stat('cheats',$game_id);
                $p = $this->_delete_stat('plays',$game_id);
                $s = $this->_delete_stat('shots',$game_id);
                return $w && $l && $c && $p && $s;
        }
        /**
         * helper function to rank the stats table
         */
        private function _rank(&$table, $column)
        {
            $prev = null;
            
            $length = count($table);
            for($i = 0; $i < $length; $i++)
            {
                
                $sub_arr = $table[$i][$column];
                
                if($prev == null || $sub_arr == $prev)
                {
                    $table[$i]['rank'] = $i == 0 ? 1 : $table[$i - 1]['rank'];
                }
                else
                {
                    $table[$i]['rank'] = $i + 1;
                }
                $prev = $sub_arr;
            }
        }



        //--------- Boolean status functions ---------------
        /**
         * is game finished
         */
        function isGameFinished($game_id)
        {
            $where = array('finished' => "1",
                           'id'  => $game_id);
            return $this->db->select('game',array('*'), $where);

        }
        
        function isValidGameCode($game_id)
        {
            return $this->db->select('game', array('*'), array('id'=> $game_id));
        }

        /**
         * 
         */
        function isPlaying($user_id, $game_id)
        {
            $players = $this->getPlayersIds($game_id);
            foreach($players as $pp => $player_id)
            {
                if ($player_id == $user_id)
                    return True;
            }
            return False;
        }

        
        //------------ Getters ---------------------
        /**
         * show  stats
         */
        function getStats($column = 'wins', $type=SORT_DESC)
        {
            $table  = $this->db->select('stats', array('*'));
            
            $c = array_map(function($row) use ($column){return $row[$column];}, $table);
            array_multisort($c, $type, $table);

            $this->_rank($table, $column);
            return $table;
        }
        
        /**
         * show game hands
         */
        function getHands($game_id)
        {
            return $this->db->select('hand', array('*'), array('game_id' => $game_id),'AND', 'ORDER BY time');
        }
        /**
         * associative arrays of players ids playing game_id
         */
        function getPlayersIds($game_id)
        {
            $players = array('player1' => null,
                             'player2' => null,
                             'player3' => null,
                             'player4' => null);

            $where   = array( 'id' => $game_id );
            $rows    = $this->db->select('game', array('*'), $where);
            if ($rows)
            {
                return array_intersect_key($rows[0], $players);
            }
            return $players;
        }
        

        /**
         * associative array of players names
         */
        function getPlayersUsernames($game_id)
        {
            $playersIds = $this->getPlayersIds($game_id);
            foreach ($playersIds as $player => $id)
            {
                $result = $this->db->select('player', array('username'), array('id'=> $id));
                if ($result)
                    $playersIds[$player] = $result[0]['username'];
            }
            return $playersIds;
        }


        //--------------Setters and Modifiying game functions ------
        /***
         *  A user tries to join a game
         */
        function join($user_id, $game_id)
        {
            $joined =  $this->isPlaying($user_id, $game_id);
            
            if ($joined)
                return True;
            else 
            {  
                $possible_players = array('player2', 'player3', 'player4');
                $where            = array( 'id' => $game_id );

                //Checking for available spots 
                //available spots are NULL values in the game table
                foreach ($possible_players as $index => $player)
                {
                    $set   = array($player => $user_id);
                    $nulls = array($player);
    
                    if ($this->db->updateIfNull('game', $set, $where, $nulls))
                        return True;
                }
            }
            return False;
        }
        /**
         * A user creates a game. Returns the id of the created game.
         */
        function create($user_id)
        {
            $values = array(
                'player1' => $user_id,
                'player2' => null,
                'player3' => null,
                'player4' => null,
                'time_start' => time(),
                'time_end'   => null,
                'finished'   => 0
            );
            return $this->db->insert('game', $values);
        }
        /*
        *   Adds a hand score to game_id
        */
        function addHand($user_id, $game_id, $player1_score, $player2_score, $player3_score, $player4_score)
        {
            $hand_added = 0;

            if ( $this->isPlaying($user_id, $game_id) &&
                !$this->isPlaying(null    , $game_id) && //All players are playing
                !$this->isGameFinished($game_id))
            {
                
                $total_score = array_sum(array($player1_score, 
                                                $player2_score, 
                                                $player3_score, 
                                                $player4_score));
                
                //The possible total scores are 26 or 26 * 3
                if ( ($total_score !=  26 ) && ($total_score != (26 * 3)) )
                    return $hand_added;
                //At least a player must have 13 points or more
                
                if (($player1_score < 13) &&
                    ($player2_score < 13) &&
                    ($player3_score < 13) &&
                    ($player4_score < 13)   )
                    {
                       
                        return $hand_added;
                    }
                        
                
                $values = array(
                    'game_id'       => $game_id,
                    'player1_score' => $player1_score,
                    'player2_score' => $player2_score,
                    'player3_score' => $player3_score,
                    'player4_score' => $player4_score,
                    'time'          => time()
                );
                return $this->db->insert('hand', $values);
            }
            return $hand_added;
        }

        /*
        *   Deletes a hand from a game if the user is playing the game
        */
        function deleteHand($user_id, $game_id, $hand_id)
        {
            if ($this->isPlaying($user_id, $game_id) &&
                !$this->isGameFinished($game_id))
            {
                $where = array( 'id' => $hand_id );
                return $this->db->delete('hand', $where);
            }
            return False;
        }
        /**
         * finis a game and stores the stats:
         *  wins, losses, plays, shots, cheats
         */
        function finish($user_id, $game_id)
        {
            if ( $this->isPlaying($user_id, $game_id) &&
                !$this->isGameFinished($game_id))
            {

                $this->_clear_game_stats($game_id);

                $players = $this->getPlayersIds($game_id);
                $scores  = $this->db->select('hand',
                                            array('sum(player1_score) as player1_score',
                                                  'sum(player2_score) as player2_score',
                                                  'sum(player3_score) as player3_score',
                                                  'sum(player4_score) as player4_score'),
                                            array('game_id' => $game_id));
                if ($scores)
                {
                    $scores  = $scores[0];
                
                    $data = array_map(function($player, $player_id) use ($scores) {
                            return array('score'  => $scores[$player.'_score'], 
                                         'id'     => $player_id);
                    }, array_keys($players), $players);
                    
                    //$ids    = map_array($data, function($row){return $row['id'];});
                    $scores = array_map(function($row){return $row['score'];}, $data);

                    
                    array_multisort($scores, SORT_ASC, $data);
                    
                    $min_score = $scores[0];
                    $max_score = end($scores);
                    
                    reset($scores);

                    foreach ($data as $index => $row)
                    {
                        if ($row['score'] == $min_score)
                            $this->_insert_stat('wins', $game_id, $row['id']);
                        if ($row['score'] == $max_score)
                            $this->_insert_stat('losses', $game_id, $row['id']);
                        $this->_insert_stat('plays', $game_id, $row['id']);
                    }
                    
                }
                
                $hands   = $this->db->select('hand',
                                             array('player1_score',
                                                   'player2_score',
                                                   'player3_score',
                                                   'player4_score'),
                                            array('game_id' => $game_id));
                if ($hands)
                {
                    $keys = array('player1', 'player2', 'player3', 'player4');
                    $hands = array_map(function($index, $hand) use ($players, $keys) {
                        

                        return [
                            0 => array( 
                                'id'    => $players[$keys[0]],
                                'score' => $hand[$keys[0].'_score'],
                                'column'=> $keys[0]
                            ),
                            1 => array( 
                                'id'    => $players[$keys[1]],
                                'score' => $hand[$keys[1].'_score'],
                                'column'=> $keys[1]
                            ),
                            2 => array( 
                                'id'    => $players[$keys[2]],
                                'score' => $hand[$keys[2].'_score'],
                                'column'=> $keys[2]
                            ),
                            3 => array( 
                                'id'    => $players[$keys[3]],
                                'score' => $hand[$keys[3].'_score'],
                                'column'=> $keys[3]
                            )
                        ];
                    }, array_keys($hands), $hands);

                    foreach ($hands as $index => $hand)
                    {
                        $ids    = array_map(function ($value){ return $value['id'];}, $hand);
                        $scores = array_map(function ($value){ return $value['score'];}, $hand);
                        $column = array_map(function ($value){ return $value['column'];}, $hand);
                        array_multisort($scores, $ids, $column);
                        

                        $total = array_sum($scores);
                        $last_index = 3;

                        if ($total == (26 * 3))
                        {
                            $this->_insert_stat('shots', $game_id, $ids[0]);
                        }
                        if ($total == 26 && $scores[$last_index] == 26)
                        {
                            $this->_insert_stat('cheats', $game_id, $ids[$last_index]);
                        }
                            
                    }
                    /**
                     * finish game
                     */
                    if ($scores)
                    {
                        $set   = array('time_end' => time(),
                                        'finished' => 1   );
                        $where = array('id'       => $game_id);
                        $this->db->update('game', $set, $where);
                    }
                    
                   
                }
                return True;
            }
            return False;
        }
        function unlockGame($user_id, $game_id)
        {
            $player = new Player($this->db);
            $admin = $player->isAdmin($user_id);
            if ($admin)
            {
                $this->db->update('game', array('finished'=> 0), array('id'=>$game_id));
            }
            return $admin;
        }
}

 
?>
