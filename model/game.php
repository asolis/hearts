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
        /**
         * helper function to delete an invite
         */
        function _delete_invite($user_id, $invited_id, $game_id)
        {
            $where = array('game_id' => $game_id,
                            'user_id' => $user_id,
                            'invited_id' => $invited_id);

            return  $this->db->delete('invites', $where );
        }

        /**
         * helper function to compute elo among players in one game
         * $game = array(
         *  array('player_id' => id, 'score'=> score, 'elo'=>elo, 'played'=>games_played),
         *  array('player_id' => id, 'score'=> score, 'elo'=>elo, 'played'=>games_played),
         *  array('player_id' => id, 'score'=> score, 'elo'=>elo, 'played'=>games_played),
         *  array('player_id' => id, 'score'=> score, 'elo'=>elo, 'played'=>games_played)
         * )
         */
        function _elo_4_($game, $exp = 400)
        {
            foreach(range(0,3) as $player1)
            {
                $elo1   = $game[$player1]['elo'];
                $score1 = $game[$player1]['score'];
                $played = $game[$player1]['played'];

                $R1     = 10 ** ($elo1/$exp);
                
                $AdjustK    = ($played < 5)? 2.0 : ( ($played < 10)? 4.0: 8.0 );
                
                $compound1  = 0.0;
                foreach(range(0,3) as $player2)
                {
                    if ($player1 == $player2)
                        continue;
                    
                    $elo2   = $game[$player2]['elo'];
                    $score2 = $game[$player2]['score'];

                    $R2   = 10 ** ($elo2/$exp);
                    $E1   = $R1 / ($R1 + $R2);

                    $S1   = ($score1 < $score2)? 1.0 : ( ($score1 == $score2)? 0.5 : 0.0);
                    
                    $compound1 += $AdjustK * ($S1 - $E1);
                }
                $game[$player1]['comp_elo'] = intval( $elo1 + round($compound1));
            }
                
            return $game;
        }
        
        function _history_elo_()
        {
            $history = $this->db->select('stats_game_hand',
                                        array('player_id',
                                                'game_id',
                                                'sum(score) as score'),
                                        array(),
                                        'AND',
                                        'group by game_id, player_id order by time_end');
            if ($history)
            {
                $total_games = count($history);
                if ($total_games % 4 != 0)
                    die('Wrong game table');
                
                $players = $this->db->select('player',
                                             array('id',
                                                   'elo',
                                                   'first_name',
                                                   'last_name',
                                                   'username'),
                                            array('admin'=>0));
                
                $elo = array();
                array_walk($players, function(&$row, $idx) use (&$elo){ 
                    $elo[$row['id']]= array(
                                    'elo'   => $row['elo'], 
                                    'played'=> 0,
                                    'id'    => $row['id'],
                                    'first_name' => $row['first_name'],
                                    'last_name'  => $row['last_name'],
                                    'username'   => $row['username']);
                });
                
                foreach (range(0,$total_games-1,4) as $idx)
                {
                    $elo[$history[$idx]['player_id']]['played']     += 1;
                    $elo[$history[$idx + 1]['player_id']]['played'] += 1;
                    $elo[$history[$idx + 2]['player_id']]['played'] += 1;
                    $elo[$history[$idx + 3]['player_id']]['played'] += 1;

                    $history[$idx]['elo']     = $elo[$history[$idx]['player_id']]['elo'];
                    $history[$idx + 1]['elo'] = $elo[$history[$idx + 1]['player_id']]['elo'];
                    $history[$idx + 2]['elo'] = $elo[$history[$idx + 2]['player_id']]['elo'];
                    $history[$idx + 3]['elo'] = $elo[$history[$idx + 3]['player_id']]['elo'];

                    $history[$idx]['played']     = $elo[$history[$idx]['player_id']]['played'];
                    $history[$idx + 1]['played'] = $elo[$history[$idx + 1]['player_id']]['played'];
                    $history[$idx + 2]['played'] = $elo[$history[$idx + 2]['player_id']]['played'];
                    $history[$idx + 3]['played'] = $elo[$history[$idx + 3]['player_id']]['played'];

                    $game = array($history[$idx], 
                                  $history[$idx + 1], 
                                  $history[$idx + 2], 
                                  $history[$idx + 3]);
                    $game = $this->_elo_4_($game);

                    $elo[$history[$idx]['player_id']]['elo']    = $game[0]['comp_elo'];
                    $elo[$history[$idx + 1]['player_id']]['elo']= $game[1]['comp_elo'];
                    $elo[$history[$idx + 2]['player_id']]['elo']= $game[2]['comp_elo'];
                    $elo[$history[$idx + 3]['player_id']]['elo']= $game[3]['comp_elo'];
                }
                
                
                $shots = $this->db->select('shots', array('player_id','count(player_id) as shots'),array(),'AND',' group by player_id');
                array_walk($shots, function($shot, $idx) use (&$elo){
                    $elo[$shot['player_id']]['elo'] += intval($shot['shots']);
                });
                
                $cheats = $this->db->select('cheats', array('player_id','count(player_id) as cheats'),array(),'AND',' group by player_id');
                array_walk($cheats, function($cheat, $idx) use (&$elo){
                    $elo[$cheat['player_id']]['elo'] -= intval($cheat['cheats']);
                });

                return $elo;
            }
            
            return array();
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
        function getElo($column = 'elo', $type=SORT_DESC)
        {
            $table  = $this->_history_elo_();
            
            $c = array_map(function($row) use ($column){return $row[$column];}, $table);
            array_multisort($c, $type, $table);

            $this->_rank($table, $column);
            
            return $table;
        }
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
         * show  stats ext
         */
        function getStatsExt($column = 'avg_hand', $type=SORT_ASC)
        {


            $table  = $this->db->select('stats_game_hand', array('player_id',
                                                                 'avg(score) as avg_hand',
                                                                 'cast (sum(score) as float)/count (distinct game_id) as avg_game',
                                                                 'sum(score) as total_pts',
                                                                 'count (distinct game_id) as games',
                                                                 'first_name',
                                                                 'last_name',
                                                                 'username'), array(),'AND','group by player_id');
            
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


        function inviteToSwitch($user_id,$invited_id, $game_id )
        {
            if (!$this->isGameFinished($game_id) &&
                 $this->isPlaying($user_id, $game_id))
            {
                $values = array('game_id'    => $game_id, 
                             'user_id'    => $user_id,
                             'invited_id' => $invited_id );
                return $this->db->insert('invites', $values);
            }
            return False;
        }
        function isInvitedToJoin($invited_id,$game_id)
        {
            if ($this->isGameFinished($game_id))
                return False;
            return $this->db->select('invites',array('*'), array('game_id'   => $game_id,
                                                      'invited_id'=> $invited_id));
        }

        function getOpenInvitations($invited_id)
        {
            $invitations = $this->db->select('invitations', array('*'), array(
                'invited_id' => $invited_id
            ));
            foreach ($invitations as $idx => &$invitation)
            {
                $tmp = $this->db->select('player',array('*'), array('id' => $invitation['user_id']));
                if ($tmp)
                {
                    $invitation['username']   = $tmp[0]['username'];
                    $invitation['first_name'] = $tmp[0]['first_name'];
                    $invitation['last_name']  = $tmp[0]['last_name'];
                }
                
            }
            return $invitations;

        }


        function declineSwitchInvite($user_id, $invited_id, $game_id)
        {
            $success = False;
            $invitation = $this->isInvitedToJoin($invited_id, $game_id);
            
            if ($invitation)
            {
                if ($user_id == $invitation[0]['user_id'])
                {
                    $success = $this->_delete_invite($user_id, $invited_id, $game_id);
                }
                
            }
            return $success;

        }

        function acceptSwitchInvite($user_id, $invited_id, $game_id)
        {
            $success = False;
            //only can accept invitation from open games
            //check for invitaiton
            $invitation = $this->isInvitedToJoin($invited_id, $game_id);

            if ($invitation)
            {
                //if there is an invitation check who invited and update
                $ids = $this->getPlayersIds($game_id);
                foreach ($ids as $player => $id)
                {
                    //if the user that invitated is playing 
                    //make the switch
                    
                    if ($id == $invitation[0]['user_id'] &&
                        $id == $user_id)
                    {
                        $set   = array($player   => $invited_id);
                        $where = array('id'      => $game_id );
                                        
                        
                        $switched = $this->db->update('game', $set, $where);
                        if ($switched)
                        {
                            $success = $this->_delete_invite($user_id, $invited_id, $game_id);
                        }
                        break;
                    }
                }
                
                
            }
            return $success;
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
