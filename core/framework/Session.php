<?php
//require_once("Entity.php");
import("core.data.DBEntity");

/**		
 * @author Matteo Vigoni <mattevigo@gmail.com>																			
 * @package DBEntity
 * 
 * @version 3.0																									 
 */
class Session extends DBEntity
{	
    /**
     * Create a new PHP session and store it in the database
     * 
     * @param DB $db_wrapper
     * @param string $sid the session id
     * @param string $uid the user id
     * 
     * @throws SessionException if a session with that parameters already exist
     * @throws DBException for problem with the database
     * 
     * @todo questo costruttore dovrebbe non essere pubblico in quanto vorrei prevedere una funzione statica
     * 		che restituisca un oggetto di tipo sessione, questo perche' oltre a gestire i record di sessione
     * 		devo gestire anche i cookies al browser.
     */
    public function __construct(DB $db, $sid, $uid)
    {	
        //controllo se esiste gia' una sessione attiva con questo SID
        if(Session::validate($db, $sid, $uid, $db->session_time))
        {
            throw new SessionException("The session ".$sid." is valid.");
        }

        parent::__construct($db->sessions, "ID", $db);

        //$this->set("session_id", $sid);
        $this->set("user_id", $uid);
        $this->set("start", time());
        $this->set("time", time());
        $this->set("user_agent", $_SERVER['HTTP_USER_AGENT']);
        $this->set("ip", $_SERVER['REMOTE_ADDR']);

        $this->save($sid); //salva le modifiche sul database
    }
	
    /**
     * Check if already exist a session with this SID
     * 
     * @param DB $db_wrapper
     * @param string $table_name
     * @param string $sid
     * 
     * @return true if a session exist, false otherwise
     * 
     * @throws DBException for problem connecting the database
     */
    public static function exist(DB $db, $sid)
    {	
        $mysql_query = "SELECT count(*) FROM $db->sessions WHERE ID='$sid'";
        $data = mysql_fetch_row($db_wrapper->query($mysql_query));

        if($data[0]==0)
            return false;
        else 
            return true;
    }
	
    /**
     * Validate a session
     *
     * @param string $sid the session id
     * @param string $uid the user id
     * @param DB $db_wrapper 
     * @param $session_time session length (in seconds), if $session_time is 0 (default)
     * 			there is no time limit
     * 
     * @throws DBException for problem with the query
     * 
     * @return true if the $sid session is valid, false otherwise
     */
    public static function validate(DB $db, $sid, $uid, $session_time=0)
    {	
        $mysql_query =  "SELECT `user_id`, `time` " .
                        "FROM `$db->sessions` " .
                        "WHERE `ID` = '$sid' " .
                        "ORDER BY start DESC";
        //echo $mysql_query."<br />";

        $result = $db->query($mysql_query);

        $row = mysql_fetch_assoc($result);

        if($row['user_id'] != $uid)
            return false;
        else if(($session_time > 0) && ($row['session_time'] + $session_time < time()))
            return false;
        else 
            return true;
    }
	
}

class SessionException extends EntityException{
	
	function __construct($message){
		parent::__construct($message);
	}
}
?>
