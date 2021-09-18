<?php

/**
 * Some classes to simplify communication between php and database
 *
 * @author     Xorrad <monsieurs.aymeric@gmail.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html  GNU General Public License v3.0
 * @version    2.0
 */

class Database
{
    /**
     * This is the name attribute.
     * @var object
     */
    public $pdo;

    /**
     * Connection statut
     * @var boolean
     */
    private $connected;

    /**
     * Last exception triggered
     * @var object
     */
    private $last_error;

    /**
     * Last sql query executed
     * @var object
     */
    private $last_query;

    function __construct()
    {
        $this->connected = false;
        $this->last_query = "";
    }
    
    /**
       * 
       * Get the PDO object
       *
       * @return object
       */
    function get_pdo()
    {
        return $this->pdo;
    }

    /**
       * 
       * Get the last exception triggered
       *
       * @return object
       */
    function get_last_error()
    {
        return $this->last_error;
    }

    /**
       * 
       * Get the last query executed
       *
       * @return object
       */
      function get_last_query()
      {
          return $this->last_query;
      }

    /**
       * 
       * Get the statut of the connection
       *
       * @return boolean
       */
    function is_connected()
    {
        return $this->connected;
    }

    /**
       * 
       * Connect to the database using credentials
       *
       * @param string $host  address of the database
       * @param string $database  name of the database
       * @param string $user  username
       * @param string $password  password
       * @return boolean
       */
    function connect($host, $database, $user, $password)
    {
        try 
        {
            $this->pdo = new PDO("mysql:host=".$host.";dbname=".$database, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $this->connected = true;
        }
        catch (PDOException $e)
        {
            $this->connected = false;
            $this->last_error = $e;
        }
        
        return $this->connected;
    }

    /**
       * 
       * Close the connection to database
       *
       * @return void
       */
    function disconnect()
    {
        $this->pdo->query('KILL CONNECTION_ID()');
        $this->pdo = null;
        $this->connected = false;
    }

    /**
       * 
       * Check if a table exists
       *
       * @param string $name table's name
       * @return boolean
       */
    function table_exists($name)
    {
        $query = $this->pdo->prepare("SHOW TABLES LIKE '$table'");
        $query->execute();
        $row_count = $query->rowCount();
        $query->closeCursor();
        return ($row_count > 0);
    }

    /**
       * 
       * Check if a column exists
       *
       * @param string $table table's name
       * @param string $column column's name
       * @return boolean
       */
    function column_exists($table, $column)
    {
        $query = $this->pdo->prepare("SHOW COLUMNS FROM $table LIKE '$column'");
        $query->execute();
        $row_count = $query->rowCount();
        $query->closeCursor();
        return ($row_count > 0);
    }
    
    /**
       * 
       * Get the key of a column (primary, unique...)
       *
       * @param string $table table's name
       * @param string $column column's name
       * @return string
       */
    function get_column_key($table, $column)
    {
        $query = $this->pdo->prepare("SHOW INDEX FROM $table WHERE Column_name = '$column'");
        $query->execute();
        $column_key = $query->fetch()['Key_name'];
        $query->closeCursor();
        return $column_key;
    }

    /**
       * 
       * Execute a query to the database
       *
       * @param string $req sql query as string
       * @param array $args optional array of arguments
       * @return object
       */
    function execute($raw_query, $args = array())
    {
        //Initialize the query object
        $final_query = new Query($raw_query, $args);

        try 
        {
            //Prepare the pdo query
            $query = $this->pdo->prepare($final_query->get_query());
            $query->execute($final_query->get_args());

            //Retrieve the values depending on the query type
            if($final_query->type == 'SELECT')
            {
                $final_query->count = $query->rowCount();
                $final_query->rows = $query->fetchAll(PDO::FETCH_ASSOC);
                $where_columns = $final_query->get_where_columns($this);

                //Check if there is supposed to be always only one row, if the query search for a primary column
                foreach($where_columns as $column)
                {
                    $key =  $this->get_column_key($final_query->table, $column);
                    if($key == 'PRIMARY')
                    {
                        $final_query->rows = $final_query->rows[0];
                        break;
                    }
                }
            }
            else if($final_query->type == 'INSERT')
            {
                $final_query->last_insert_id = $this->pdo->lastInsertId();
            }

            $query->closeCursor();
        }
        catch (PDOException $e) 
        {
            $this->last_error = $e;
            $final_query->error = $e;
        }

        $this->last_query = $final_query;

        return $final_query;
    }

}

class Query
{
    public $type;
    public $table;
    public $query;
    public $args;

    public $rows;
    public $count;
    public $last_insert_id;

    public $error;

    function __construct($req, $args) 
    {
        $this->query = $req;
        $this->args = $args;
        $this->rows = array();
        $this->count = 0;
        $this->error = null;
        $this->type = explode(' ', $req)[0];
        $this->table = $this->get_table();
        $this->last_insert_id = 0;
    }

    /**
     * 
     * Get the type sql query (select, insert, update...)
     *
     * @return string
     */
    function get_type()
    {
        return $this->type;
    }

    /**
     * 
     * Get the sql query as string
     *
     * @return string
     */
    function get_query()
    {
        return $this->query;
    }

    /**
     * 
     * Get the sql query arguments in an array
     *
     * @return array
     */
    function get_args()
    {
        return $this->args;
    }

    /**
     * 
     * Get the id of the latest inserted row
     *
     * @return integer
     */
    function get_last_insert_id()
    {
        return $this->last_insert_id;
    }

    /**
     * 
     * Check if the query has been executed successfully
     *
     * @return boolean
     */
    function has_succeeded()
    {
        return $this->error == null;
    }

    /**
     * 
     * Get the exception that has been triggered
     *
     * @return object
     */
    function get_error()
    {
        return $this->error;
    }

    /**
     * 
     * Get the number of rows
     *
     * @return integer
     */
    function get_count()
    {
        return $this->count;
    }

    /**
     * 
     * Get the rows
     *
     * @return array
     */
    function get_rows()
    {
        return $this->rows;
    }

    /**
     * 
     * Get query target table
     *
     * @return string
     */
    function get_table()
    {
        $parts = explode(' ', $this->query);

        switch($this->type)
        {
            case 'SELECT':
                $is_from_block = false;
                foreach($parts as $part)
                {
                    if($is_from_block)
                        return $part;

                    if($part == 'FROM')
                        $is_from_block = true;
                }
                break;

            case 'INSERT':
                return $parts[2];

            case 'DELETE':
                return $parts[2];

            case 'UPDATE':
                return $parts[1];
        }

        return '';
    }

    /**
     * 
     * Get query where columns
     *
     * @return array
     */
    function get_where_columns($database)
    {
        $columns = array();
        $parts = explode(' ', $this->query);
        $is_where_block = false;

        foreach($parts as $part)
        {
            if($is_where_block)
            {
                if($part !== '=' && $part !== '?' && $part !== 'AND')
                {
                    if($database->column_exists($this->table, $part))
                    {
                        array_push($columns, $part);
                    }
                    else
                    {
                        $is_where_block = false;
                    }
                }                
            }

            if($part == 'WHERE')
                $is_where_block = true;
        }

        return $columns;
    }
}

?>
