<?php
import("core.data.DBEntity");

/**
 * @author Matteo Vigoni <mattevigo@gmail.com>
 * @package core.framework
 * @version 3.0
 */
class User extends DBEntity
{	
    /**
     * Costruttore
     */
    public function __construct(DB $db, $uid)
    {
        parent::__construct($db->users, "ID", $db, $uid);
    }

    /**
     * Cambia la password utente dopo aver verificato la validita' della vecchia password
     *
     * @param string $old_password
     * @param string $new_password
     *
     * @throws DBException per problemi con il database
     * @throws EntityException se la vecchia password inserita e' sbagliata
     *
     */
    public function setPassword($old_password, $new_password)
    {
        //echo $old_password . " / ".$new_password . "<br />";
        //echo "DB:". $this->db_wrapper->t_users;
        $mysql_query =  "SELECT `user_password` ".
                        "FROM `{$this->getDB()->name()}`.`{$this->getDB()->users}` ".
                        "WHERE `user_id`={$this->getId()}";
        //echo $mysql_query;
        $result = $this->getDB()->query($mysql_query);
        $row = mysql_fetch_row($result);
        //echo $row[0] . "<br />";
        //echo $old_password . "<br />";

        if( strcmp($old_password, $row[0]) == 0 )
        {

            $mysql_query =  "UPDATE `{$this->getDB()->name()}`.`{$this->getDB()->users}` ".
                            "SET `user_password`='$new_password' ".
                            "WHERE `user_id`={$this->getId()}";

            $this->getDB()->query($mysql_query);

        } else throw new EntityException("Wrong Password");
    }

    /**
     * 
     * @param $new_email
     * @return unknown_type
     */
    public function setEmail($new_email)
    {
            $this->set('email', $new_email);
    }

    public function getUsername()
    {
            return parent::get('username');
    }
	
    public static function getUsernameFromId( DB $db, $id )
    {
        $sql = "SELECT user_username FROM ".$db->t_users." WHERE ID=$id";

        $result = $db->query( $sql );
        $user = mysql_fetch_assoc( $result );

        return $user['user_username'];
    }
	
    public static function getUserFromId( DB $db, $id )
    {
        $userobj = new User($db, $id);
        return $userobj;
    }

    /**
        * Funzione di login, verifica username e password e se questi coincidono con i relativi valori del database
        * viene restituito un oggetto di tipo <code>User</code> relativo all'utente
        *
        * @param DB $db_wrapper
        * @param string $username
        * @param string $password
        *
        * @throws 	DBException se si sono verificati problemi con il database
        * 			LoginException
        * @return un  nuovo <code>User</code>
        */
    public static function login(DB $db, $username, $password)
    {
        $user = null;
        $db_query = "SELECT `ID` , `password` ".
                    "FROM `$db->users` ".
                    "WHERE `username` = '$username'";

        //echo $db_query."<br />";
        
        $result = $db->query($db_query);
        $row = mysql_fetch_row($result);
        //echo "user: " . $row[0] . "<br />";

        if(count($row) != 2)
        {
            throw new LoginException("Wrong Username");
        }
        else if(strcmp($password, $row[1]) == 0)
        {
            $user = new User($db, $row[0]);
        }
        else throw new LoginException("Wrong Username or Password");

        return $user;
    }
}

class LoginException extends Exception
{
	function __construct($message){
		parent::__construct($message);
	}
}
?>
