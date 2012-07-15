<?php
import("core.data.DBEntity");

/**		
 * @author Matteo Vigoni <mattevigo@gmail.com>																			
 * @package Formigli S.r.L.																						 
 */
class Cart extends DBEntity
{
	const TABLE = 'formigli_preventivo_carrello';
	const PRIMARY_KEY = 'carrello_id';
	
	/**
	 * Costruttore
	 * 
	 * @todo test con il prefix
	 */
	public function __construct(DB $db, $id=null)
	{
		parent::__construct($db, self::TABLE, self::PRIMARY_KEY, $id);	
	}
	
	/**
	 * Restituisce il carrello relativo alla $session_id
	 * @param DB $db
	 * @param unknown_type $session_id
	 * @throws DBEntityException se il carrello non esiste (carrello vuoto)
	 */
	public static function getCartFromSessionId(DB $db, $session_id)
	{
		if(($cart_id = Cart::getCartIdFromSessionId($db, $session_id)) != null)
		{
			//echo $cart_id."<br />";
			return Cart::getCartFromId($db, $cart_id);
		}
		else 
		{
			throw new DBEntityException("Il preventivo e' vuoto");
		}
	}
	
	/**
	 * Restituisce il carrello con id $id
	 * @param DB $db
	 * @param unknown_type $id
	 */
	public static function getCartFromId(DB $db, $id)
	{
		return new Cart($db, $id);
	} 
	
	/**
	 * Restituisce l'id del carrello relativo alla session con $session_id
	 * @param DB $db
	 * @param unknown_type $session_id
	 */
	public static function getCartIdFromSessionId(DB $db, $session_id)
	{
		$mysql_query = "SELECT carrello_id FROM formigli_preventivo_carrello WHERE session_id='$session_id' ORDER BY carrello_id DESC LIMIT 0,1";
		
		//echo $mysql_query;
		
		$result = $db->query($mysql_query);
		$row = mysql_fetch_assoc($result);
		
		return $row['carrello_id'];
	}
	
	/**
	 * LAZY
	 * Restituisce le voci di preventivo relative a questo carrello
	 */
	public function getRows()
	{
		$items = array();
		
		$primary_key = 'carrello_id';
		$table = "formigli_preventivo_voci";
		$pk = parent::get($primary_key);
		
		$mysql_query =  "SELECT voce_id " .
						"FROM formigli_preventivo_voci " .
						"WHERE carrello_id=$pk ";
		
		//echo $mysql_query;
		
		$result = $this->db_wrapper->query($mysql_query);
		
		$count = 0;
		while($row = mysql_fetch_assoc($result))
		{
			$items[$count] = new Row($this->db_wrapper, $row['voce_id']);
			$count++;
		}
		
		return $items;
	}
	
	/**
	 * Conta il numero di voci di preventivo presenti nel carrello
	 */
	public function countRows()
	{
		$table = "formigli_preventivo_voci";
		
		$mysql_query =  "SELECT count(*) " .
						"FROM $table " .
						"WHERE carrello_id='{$this->getId()}' ";
		
		$result = $this->db_wrapper->query($mysql_query);
		$row = mysql_fetch_assoc($result);
		
		return $row['count(*)'];
	}
	
	/**
	 * Restituisce un array dei DBEntity che rappresentano le voci di questo carrello
	 * @deprecated
	 * @return unknown_type
	 */
	public function getItems()
	{
		$items = array();
		
		$primary_key = 'session_id';
		$table = "formigli_preventivo_voci";
		$pk = parent::get($primary_key);
		
		$mysql_query =  "SELECT voce_id " .
						"FROM $table " .
						"WHERE $primary_key='$pk' ";
		
		$result = $this->db_wrapper->query($mysql_query);
		
		$count = 0;
		while($row = mysql_fetch_assoc($result))
		{
			$items[$count] = new DBEntity($this->db_wrapper, $table, 'voce_id', $row['voce_id']);
			$count++;
		}
		
		return $items;
	}
	
	/**
	 * Conta il numero di voci del carrello
	 * @deprecated
	 * @return unknown_type
	 */
	public function countItems()
	{
		$primary_key = 'session_id';
		$table = "formigli_preventivo_voci";
		$pk = parent::get($primary_key);
		
		$mysql_query =  "SELECT count(*) " .
						"FROM $table " .
						"WHERE $primary_key='$pk' ";
		
		$result = $this->db_wrapper->query($mysql_query);
		$row = mysql_fetch_assoc($result);
		
		return $row['count(*)'];
	}
	
	/**
	 * Interroga il database e restituisce la sigla corrispondente al colore_id
	 */
	public static function getColoreSiglaFromColoreId(DB $db_wrapper, $colore_id )
	{
		$mysql_query = "SELECT colore_sigla FROM formigli_colori WHERE colore_id=$colore_id";
		
		$result = $db_wrapper->query($mysql_query);
		$row = mysql_fetch_row($result);
		
		return $row[0];
	}
	
	/**
	 * Interroga il database e restituisce la sigla corrispondente al colore_id
	 */
	public static function getColoreCodiceFromColoreId(DB $db_wrapper, $colore_id )
	{
		$mysql_query = "SELECT colore_codice FROM formigli_colori WHERE colore_id=$colore_id";
		
		$result = $db_wrapper->query($mysql_query);
		$row = mysql_fetch_row($result);
		
		return $row[0];
	}
	
	/**
	 * Interroga il database e restituisce l'indirizzo dell'immagine corrispondente al colore_id
	 */
	public static function getColoreThumbFromColoreId(DB $db_wrapper, $colore_id )
	{
		$mysql_query = "SELECT colore_img_url FROM formigli_colori WHERE colore_id=$colore_id";
		
		$result = $db_wrapper->query($mysql_query);
		$row = mysql_fetch_row($result);
		
		return $row[0];
	}
	
	/**
	 * Converte il session_id nella chiave primaria del carrello carrello_id
	 */
	public static function getCarrelloIdFromSessionId(DB $db, $session_id)
	{
		$mysql_query = "SELECT carrello_id FROM formigli_preventivo_carrello WHERE session_id='$session_id'";
		
		$result = $db->query($mysql_query);
		$row = mysql_fetch_row($result);
		
		return $row[0];
	}
}