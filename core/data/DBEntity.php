<?php
require_once( $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'config.php' );
import("core.framework.Object");

/**
 * @author Matteo Vigoni <mattevigo@gmail.com>
 * @package core.data
 *
 * @version 3.0
 *
 * This class is the core of the Database Abstraction Layer.
 * An istance of DBEntity rappresent a record of a generic table on the DB. You can create
 * an object with DBEntity and save it in the database, errors will be processed from the
 * DBMS, it's very important know what you are doing and the struct of the DB.
 *
 * The best way to use DBEntity is extend it for made object with your own functionality,
 * take a look at the Session and User class in the core directory for an example.
 *
 */
class DBEntity extends Object
{
    /**
     * @var $primary_key field name for primary key
     * @var $id primary key value
     */
    private $id = NULL;

    //public $values = Array();	#deprecated
    private $changes = array();

    public $is_new = true;
    public $nelem = 0;

    public $db;
    public $table = NULL;	# the name of the table that this DBEntity rappresent
    public $primary_key;

    // commit
    private $commit_query_update = "UPDATE ";
    private $commit_query_set = "SET ";
    private $commit_query_where = "WHERE ";

    /**
     * Constructor
     *
     * @param DB $db the DB object that rappresent this database
     * @param string $table the table name for this entity
     * @param string $primary_key the field name of the primary key
     *
     * @throws DBException for problem with DBMS
     *
     * @todo implementazione di chiavi primarie multiple da realizzare con due array $pk e $kv che contengono
     * 			i nomi e i valori delle chiavi primarie.
     */
    function __construct($table, $primary_key, DB $db = NULL, $id = null)
    {
        if( $db != NULL )
        {
            $this->db           = $db;
            $this->table        = $table;
            $this->primary_key  = $primary_key;

            if(isset($id))
            {
                $this->id = $id;

                $mysql_query = 	"SELECT * " .
                                "FROM $this->table " .
                                "WHERE $this->primary_key=";

                if( is_string( $this->id ) )
                {
                    $mysql_query .= "'$id'";
                }
                else
                {
                    $mysql_query .= $id;
                }

                $result = $this->db->query($mysql_query);
                $data_array = mysql_fetch_assoc($result);

                if(!$data_array) throw new EntityException("The '".$id."' row doesn't exist in ".$db->name().".".$table);

                //var_dump($data_array); # debug
                foreach($data_array as $key => $value)
                {
                    $this->set($key, $value, false);
                }

                $this->is_new = false;
            }
        }
        else
        {
            $this->is_new = false;
        }
    }

    /**
     * Default destructur
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Get the current DB
     * 
     * @return DB
     */
    public function getDB()
    {
        return $this->db;
    }

    /**
     * Set the current DB
     * 
     * @param DB $db 
     */
    public function setDB( DB $db )
    {
        $this->db = $db;
    }

    /**
     * Get the table name where this entity is mapped
     * 
     * @return string the table's name of this entity
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get the primary key name of this entity for the mapped table
     */
    public function getPrimaryKey()
    {
        return $this->primary_key;
    }

    /**
     * Get the primary key value for this DBEntity
     *
     * @return string the id for this DBEntity
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the XML rappresentation for this DBEntity
     *
     * @return string XML rappresentation for this DBEntity
     */
    public function getXML()
    {
        $xml = "<$this->table>";

        foreach($this->changes as $key => $value)
        {
            $xml .= "<$key>{$this->get($key)}</$key>";
        }
        
        $intercepted = $this->xmlInterceptor();
        if($intercepted != null)
        {
            $xml .= $intercepted;
        }

        $xml .= "</$this->table>";

        return $xml;
    }
    
    /**
     * Intercept the getXML method to coustomize the xml object returned.
     * Must return a string the will be inserted at the endo of the json object,
     * just before the closing tag.
     * 
     * @return null (by default)
     */
    public function xmlInterceptor()
    {
        return null;
    }

    /**
     * Get the JSON rappresentation for this DBEntity
     *
     * @return string JSON rappresentation for this DBEntity
     */
    public function getJSON()
    {
        $json = "{\n";
        //$i = 0;

        $isFirst = true;

        foreach($this->changes as $key => $value)
        {
            try 
            {
                $field = $this->get($key);
                $key_value = str_replace("\r\n", "", nl2br($field));

                if(!$isFirst)
                {
                    $json .= ",\n\t\"$key\":\"".addcslashes($key_value, '"')."\"";
                }
                else 
                {
                    $json .= "\t\"$key\":\"".addcslashes($key_value, '"')."\"";
                    $isFirst = false;
                }
            }
            catch (Exception $e) {}
        }

        $intercepted = $this->jsonInterceptor();
        if($intercepted != null)
        {
            $json .= $intercepted;
        }

        return $json."\n}";
    }
	
    /**
     * Intercept the getJSON method to coustomize the json object returned.
     * Must return a string the will be inserted at the endo of the json object,
     * just before the closing parenthesis '}'
     * 
     * @return null (by default)
     */
    public function jsonInterceptor()
    {
        return null;
    }

    /**
     * Fetch the field values into an associative array
     *
     * @return associative array
     */
    public function fetchAssoc()
    {
        $array = array();

        foreach( $this->changes as $key => $v )
        {
            $array[$key] = $this->$key;
        }

        return $array;
    }

    /**
     *
     * @param $array
     * @return unknown_type
     */
    public function addAssoc( $array )
    {
        // integrity check for id
        foreach( $array as $key => $value )
        {
            if( isset($this->$key) )
            {
                if( is_int( $this->$key ) && $this->$key != $value )
                    throw new DBEntityException( "The fields don't' have the same value" );

                if( is_string( $this->$key ) && strcmp($this->$key, $value) != 0 )
                    throw new DBEntityException( "The fields don't' have the same value" );
            }
        }

        // if passed set the value
        foreach( $array as $key => $value )
        {
            //echo "$key\n";
            $this->set( $key, $value, false, false );
        }
    }

    /**
     * OVERRIDE: Get the value for the generic $key field name
     *
     * @param string $key the field name
     * @throws EntityException if no value found for target key
     */
    public function get($key)
    {
        if(!isset($this->$key))
        {
            //return null;
            throw new EntityException("No value found for key '".$key."'");
        }

        return $this->$key;
    }

    /**
     * OVERRIDE: Set/Modify a record value. This modify chenge only the object and not the datbase value that
     * the object rappresent. To apply change to the database too you need to execute $this->save()
     *
     * @param string $key the field name
     * @param string $value the field values
     * @param bool update if is true (default) it will update the changes array
     * @param bool add if is true (default) value will added to the field list
     */
    public function set($key, $value, $update=true, $add=true)
    {
        $this->$key = $value;

        if( $add )
        {
            if( $update )
            {
                $this->changes[$key] = true;
                $this->nelem++;
            }
            else
            {
                $this->changes[$key] = false;
            }
        }
    }
	
    /**
     * Set the ID for this entity
     * 
     * @param type $id 
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Check if DBEntity is new (not stored yet in the DB)
     * 
     * @return unknown_type
     */
    public function isNew()
    {
        return $this->is_new;
    }

    /**
     * Apply all the changes to the backened database.
     */
    public function update()
    {
        $toSet = $this->commit_query_set;
        $first = TRUE; # variabile usata per posizionare le virgole

        foreach($this->changes as $key => $val){ # iterazione all'interno dell'array

            if($val)
            {

                if($first) {					#
                    $first = FALSE;				# Corretto posizionamento della virgola
                } else $toSet = $toSet . ", ";	#

                # viene aggiornato solo il valore cambiato
                $toSet = $toSet . "`" . $key . "`='" . $this->$key . "'";
            }

        }

        $toSet = $toSet . " ";
        //echo $toSet . "<br />";	# stampa di verifica

        $query = $this->commit_query_update . $this->table . " "
        . $toSet
        . $this->commit_query_where . "`{$this->table}`.`{$this->primary_key}`='" . $this->getId()
        . "' LIMIT 1";

        //echo $query . "<br />";	# stampa di verifica
        try{
                $this->db->query($query);
        } catch (DBException $e){
                throw new EntityException($e->getMessage());
        }

        $this->reset();
    }

    /**
     * Insert a new record on the database table that this class rappresent.
     *
     * @param string $id the primary key value, if this is null will assigned one from the DB, if AUTO_INCREMENT
     * 			is present.
     *
     * @trow EntityException se la chiave primaria e' gie' definita oppure se e' occorso un errore
     * 							durante la scrittura nel database.
     */
    public function save($id=null)
    {
        if( !$this->isNew() )
        {
            $this->commit();
            return;
        }

        $mysql_query_insert = "INSERT INTO `{$this->db->name()}`.`$this->table` (";
        $mysql_query_values = ")VALUES (";

        //echo "sid: ".$id . "<br />";

        $mysql_query_insert .= $this->primary_key . ", ";
        
        if(is_null($id))
        {
            $mysql_query_values .= "NULL, ";
        }
        else 
        {
            if(is_string($id)) $mysql_query_values .= "'$id', ";
            else $mysql_query_values .= "$id, ";
        }

        $i = 1;
        foreach($this->changes as $key => $value)
        {

            $mysql_query_insert .= $key;

            if(is_string($this->$key))
            {
                $mysql_query_values .= "'{$this->get($key)}'";
            }
            else 
            {
                try {
                        $mysql_query_values .= $this->get($key);
                } catch (Exception $e) {
                        $mysql_query_values .= "NULL";
                }
            }

            if($i < $this->nelem)
            {
                $mysql_query_insert .= ", ";
                $mysql_query_values .= ", ";
            }
            $i++;
        }

        $mysql_query = $mysql_query_insert . $mysql_query_values . ");";
        //echo $mysql_query;

        try{
            //scrivo i cambiamenti sul database
            $result = $this->db->query($mysql_query);
            if(is_null($id)) $this->id = mysql_insert_id();
            else $this->id = $id;
            //echo "ID ".$this->id."<br />";

        } catch (DBException $e){
                throw new EntityException($e->getMessage());
        }

        //resetto tutti i valori ausiliari
        $this->reset();
    }

    /**
     * Delete this record from database
     *
     * @throws DBException
     */
    public function cancel()
    {
        $mysql_query = "DELETE FROM `$this->table` WHERE `$this->table`.`$this->primary_key` = $this->id LIMIT 1";
        $this->db->query($mysql_query);
    }

    /**
     * Reset all the aux vars
     */
    private function reset()
    {
        $this->commit_query_set = "SET ";
        $this->commit_query_update = "UPDATE ";
        $this->commit_query_where = "WHERE ";

        foreach ( $this->changes as $key => $value ) 
        {
            $this->changes[$key] = false;
        }

        $this->nelem = 0;
    }

    /**
     * Count the number of record on this table
     *
     * @param DB $db the DB class for this database
     * @param string $table_name
     *
     * @return the number of record in $table_name
     */
    public static function count(DB $db, $table_name)
    {
        $mysql_query = "SELECT count(*) FROM ".$table_name." WHERE 1";
        $data = mysql_fetch_row($db->query($mysql_query));

        return $data[0];
    }

    /**
     * Return the DBEntity that rappresent the record identified from the value of $id
     * of its $primary_key
     *
     * @param DB $db the DB class for this database
     * @param string $table_name the name of the table
     * @param string $primary_key the field name of the primary key
     * @param string $id the primary key value
     * @param string $class the classname of the entity
     *
     * @return the DBEntity that rappresent the record
     */
    public static function getFromId(DB $db, $table_name=null, $primary_key=null, $id, $class="DBEntity")
    {
        //import("");
        return new $class($db, $table_name, $primary_key, $id);
    }

    /**
     * EXPERIMENTAL
     *
     * Get all Entity of a target table as an array of DBEntity filtered
     *
     * @param $db
     * @param $table
     * @param $primary_key
     * @param $classname
     * @param $orderby
     * @param $filter
     * @param $limit
     * @return unknown_type
     */
    public static function getArray($db, $table, $primary_key, $classname='DBEntity', $orderby=null, $filter=null, $limit=null)
    {
        $array = NULL;

        $mysql = "SELECT $primary_key FROM $table";

        if($filter != null)
        $mysql .= " WHERE $filter ";

        if($orderby != null)
        $mysql .= " ORDER BY $orderby ";

        if($limit != null)
        $mysql .= " LIMIT $limit";

        //echo $mysql;
        $result = $db->query($mysql);

        while( $obj = mysql_fetch_assoc($result) )
        {
            if ( $array == NULL ) $array = array();

            $array[$obj[$primary_key]] = new $classname($table, $primary_key, $db, $obj[$primary_key]);
        }

        return $array;
    }
} // End class DBEntity

/**
 *
 * @author mattevigo
 *
 */
class EntityException extends Exception
{

}

/**
 *
 * @author mattevigo
 *
 */
class DBEntityException extends Exception
{

}

/**
 *
 * @author mattevigo
 *
 */
class InvalidInputException extends Exception
{

}
