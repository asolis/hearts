<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/
class DBConnection {

    private $uri  = '';
    private $conn = null;

    function __construct($uri = 'sqlite:../db/hearts.db')
    {
        /*
        params:
            $uri:   string (e.g., sqlite:/path/to_database.db)
        */
        $this->uri  = $uri;
        $this->conn = new PDO($uri);
        $this->conn->exec( 'PRAGMA foreign_keys = ON;' );
    }
    /*
        Creates a statement from the query and binds 
        the parameters from the assoc array.

        e.g.:
            $query = 'SELECT * FROM table WHERE column1=:column1 AND column2=:column2';
            $column_value_dict = array(
                'column1': <value>,
                'column2': <value>
            );
            $stmt = null

            returns:
                a $statement object after executing the query
            
            NOTE: A statment object will be created in the parameter by reference &$stmt
    */
    function _bind_params($query, $column_value_dict, &$stmt)
    {
        $stmt = $this->conn->prepare($query);
       
        foreach ($column_value_dict as $column => &$val)
        {
            $stmt->bindParam(sprintf(':%s', $column), $val);
        }
        
        return $stmt->execute();
    }
    /*
    Takes an array of column names and returns a SQL WHERE clause
    using the specified operator for future param binding

    e.g.:
        $columns = array('column1', 'column2', 'column3');
        $op      = 'AND';

        returns:
            'WHERE column1=:column1 AND column2=:column2 AND column3=:column3'
    */
    function _where($columns, $op='AND')
    {
        $_tmp_where   = array_map(function($v) { 
                                        return sprintf(' %s=:%s ', $v, $v);
                                }, $columns);
        
        
        return sprintf('WHERE %s', implode($op, $_tmp_where));
    }
    /*
    Takes an array of column names and returns a SQL SET clause
     for future param binding

    e.g.:
        $columns = array('column1', 'column2', 'column3');

        returns:
            'SET column1=:column1 , column2=:column2 , column3=:column3'
    */
    function _set($columns)
    {
        $_tmp_set   = array_map(function($v) { 
                                        return sprintf(' %s=:%s ', $v, $v);
                                }, $columns);
        
        
        return sprintf('SET %s', implode(', ', $_tmp_set));
    }
    /*
    Takes an array of column names and returns a SQL clause
    validating that the columns are null values

    e.g.:
        $columns = array('column1', 'column2', 'column3');
        $op      = 'AND';

        returns:
            'AND column1 is null AND column2 is null  AND column3 is null'
    */
    function _null($columns, $op='AND')
    {
        $_tmp_where   = array_map(function($v) { 
                                        return sprintf(' %s IS NULL ', $v, $v);
                                }, $columns);
        
        
        return sprintf('AND %s', implode($op, $_tmp_where));
    }

    function select($table, $columns, $where = array(), $where_operator = 'AND', $append='')
    {
        $_where =  (empty($where)) ? '' : $this->_where(array_keys($where), $where_operator);
        
        $query  = sprintf('SELECT %s FROM %s %s %s', 
                                 implode(', ', $columns),
                                 $table, $_where, $append);
        
        $stmt   = null;
        $return = $this->_bind_params($query, $where, $stmt);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }

    function delete($table, $where)
    {
        $_where =  $this->_where(array_keys($where));
        
        $query  = sprintf('DELETE FROM %s %s', $table, $_where);
        $stmt   = null;
        $return = $this->_bind_params($query, $where, $stmt);
        return $stmt->rowCount(); 
    }

    function insert($table, $values)
    {
        $columns         = array_keys($values);
        $clmn_bind_names = array_map(function($v){ 
                                    return sprintf(':%s', $v);
                                }, $columns);
        
        $query      = sprintf("INSERT INTO %s (%s) VALUES (%s)",
                            $table,
                            implode(', ', $columns),
                            implode(', ', $clmn_bind_names));

        
        $auto_increment_id = 0;
        
        
        try
        {
            $this->conn->beginTransaction(); 
            $stmt   = null;
            $return = $this->_bind_params($query, $values, $stmt);
            $auto_increment_id = $this->conn->lastInsertId();
            $this->conn->commit();

        } catch(PDOExecption $e) 
        { 
            print $e;
            $this->conn->rollback(); 
        }
       
        return $auto_increment_id;
    }

    
    function update($table, $set, $where)
    {
        $query = sprintf('UPDATE %s %s %s', 
                            $table,
                            $this->_set(array_keys($set)),
                            $this->_where(array_keys($where)));
       
        
        $return = 0;
        try
        {
            $this->conn->beginTransaction(); 
            $stmt   = null;
            $this->_bind_params($query, array_merge($set, $where), $stmt);
            $this->conn->commit();
            return $stmt->rowCount();
        } catch(PDOExecption $e) 
        { 
            $this->conn->rollback(); 
        }
        return $return;
    }



    function updateIfNull($table, $set, $where, $nulls)
    {
        $query = sprintf('UPDATE %s %s %s %s', 
                            $table,
                            $this->_set(array_keys($set)),
                            $this->_where(array_keys($where)),
                            $this->_null($nulls));
        $return = 0;
        
        try
        {
            $this->conn->beginTransaction(); 
            $stmt   = null;
            $return = $this->_bind_params($query, array_merge($set, $where), $stmt);
            $this->conn->commit();
            return $stmt->rowCount();

        } catch(PDOExecption $e) 
        { 
            $this->conn->rollback(); 
        }
        return $return;
    }

}
 
?>
