<?php
import("core.data.DBEntity");

/**		
 * @author Matteo Vigoni <mattevigo@gmail.com>																			
 * @package Formigli S.r.L.																						 
 */
class Row extends DBEntity
{
	const TABLE = 'formigli_preventivo_voci';
	const PRIMARY_KEY = 'voce_id';
	
	/**
	 * Costruttore
	 * 
	 * @todo test con il prefix
	 */
	public function __construct(DB $db=null, $id=null)
	{
		parent::__construct($db, self::TABLE, self::PRIMARY_KEY, $id);	
	}
	
}