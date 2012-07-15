<?php
/**
 * @author Matteo Vigoni <mattevigo@gmail.com>
 * @package core.site
 * @version 2.0
 *
 * 
 */
/*import('core.framework.Object');
import('core.data.DB');
import('core.data.DBEntity');*/

class Set extends Object
{
	var $db;
	var $set = NULL;

	// Database
	var $query = NULL;
	var $result = NULL;
	var $affected_rows = 0;
	var $current = 0;
	var $values = NULL;
	var $classpath = 'core.framework.Object';
	
	public function __construct(DB $db)
	{
		$this->db = $db;
	}

	public function setClasspath($classpath)
	{
		$this->classpath = $classpath;
	}

	public function setQuery( $query )
	{
		$this->query = $query;
		//echo $this->query;
		
		$this->result = $this->db->query( $query );
		$this->affected_rows = mysql_affected_rows( $this->db->getLink() );
	}

	public function getNext()
	{
		//var_dump( $this->query );
		$values = mysql_fetch_assoc( $this->result );
		
		if( $values == NULL ) return NULL;
		
		$classpath = 'core.framework.Object';

		/*try
		 {
			$classpath = $values['seed_classpath'];
			}
			catch (Exception $e)
			{
			//...nothing to do!
			}*/

		$class = Set::getClassname( $classpath );

		import( $classpath );
		$obj = new $class();

		foreach( $values as $key => $value )
		{
			$obj->set( $key, $value );
		}

		$this->current++;
		return $obj;
	}

	public static function getClassname( $classpath )
	{
		//echo "<br />".$class_path;
		$exploded = explode( ".", $classpath, 10);

		$index = count( $exploded ) - 1;
		//var_dump($exploded);

		return $exploded[ $index ];
	}

	/**
	 *
	 */
	public function getSet()
	{
		return $this->set;
	}
}