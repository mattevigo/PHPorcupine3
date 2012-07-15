<?php
import("core.data.DBEntity");

/**		
 * @author Matteo Vigoni <mattevigo@gmail.com>																			
 * @package Formigli S.r.L.																						 
 */
class Upload extends DBEntity
{
	const TABLE = 'uploads';
	const PRIMARY_KEY = 'upload_id';
	
	/**
	 * Constructor
	 * 
	 * @todo test con il prefix
	 */
	public function __construct(DB $db, $id)
	{
		parent::__construct($db, $db->prefix.self::TABLE, self::PRIMARY_KEY, $id);	
	}
}