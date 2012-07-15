<?php
import("core.data.DBEntity");

/**		
 * @author Matteo Vigoni <mattevigo@gmail.com>																			
 * @package Formigli S.r.L.																						 
 */
class Prodotto extends DBEntity
{
	const TABLE = 'formigli_prodotti';
	const PRIMARY_KEY = 'prodotto_id';
	
	/**
	 * Costruttore
	 * 
	 * @todo test con il prefix
	 */
	public function __construct(DB $db=null, $id=null)
	{
		parent::__construct($db, self::TABLE, self::PRIMARY_KEY, $id);	
	}
	
	public function getColorsGroupBySection()
	{
		$colorsBySections = array();
		
		$sql = "SELECT c.colore_id, c.colore_sigla, c.colore_codice, c.colore_img_url, c.categoria_id, cc.categoria_nome, cl.linea_id, cl.linea_nome  \n"
			    . "FROM formigli_colori_prodotti cp\n"
			    . " JOIN formigli_colori c ON (c.colore_id=cp.colore_id)\n"
			    . " JOIN formigli_colori_categorie cc ON (c.categoria_id=cc.categoria_id)\n"
			    . " JOIN formigli_colori_linee cl ON (cc.linea_id=cl.linea_id)\n"
			    . "WHERE cp.prodotto_id={$this->prodotto_id}\n"
			    . "ORDER BY cl.linea_id, c.colore_id\n";
		
		$result = $this->getDB()->query($sql);
		
		while($color = mysql_fetch_assoc($result))
		{
			$colorsBySections[$color['linea_id']]['linea_nome'] = $color['linea_nome'];
			
			$colorsBySections[$color['linea_id']][$color['colore_id']]['colore_id'] = 		$color['colore_id'];
			$colorsBySections[$color['linea_id']][$color['colore_id']]['colore_sigla'] = 	$color['colore_sigla'];
			$colorsBySections[$color['linea_id']][$color['colore_id']]['colore_codice'] = 	$color['colore_codice'];
			$colorsBySections[$color['linea_id']][$color['colore_id']]['colore_img_url'] = 	$color['colore_img_url'];
			$colorsBySections[$color['linea_id']][$color['colore_id']]['categoria_id'] = 	$color['categoria_id'];
			$colorsBySections[$color['linea_id']][$color['colore_id']]['categoria_nome'] = 	$color['categoria_nome'];
			//$colorsBySections[$color['linea_id']][$color['colore_id']]['linea_nome'] = 		$color['linea_nome'];
		}
		
		return $colorsBySections;
	}
	
	/**
	 * Ritorna un array di indirizzi relativi di foto associate al prodotto
	 * 
	 * @return un array di indirizzi relativi di foto, un array vuoto se non vi sono
	 * 			foto associate
	 */
	public function getImages()
	{
		$photos = array();
		
		$query = "SELECT upload_file, upload_thumb, upload_dir, u.upload_id FROM formigli_uploads_prodotti up ".
					"JOIN formigli_uploads u ON (up.upload_id=u.upload_id) WHERE prodotto_id={$this->getId()}";
		//echo $query;

		$result = $this->getDB()->query($query);

		// Aggiunta foto al JSON
		while($row = mysql_fetch_row($result))
		{
			$photo = $row[2].$row[0];
			array_push($photos, $photo);
		}
		
		return $photos;
	}
	
	/**
	 * Conta il numero di video relativi a questo prodotto
	 */
	public function countVideos()
	{
		$counter = 0;
		
		for($i=0; $i<4; $i++)
		{
			try {
				//echo $this->get('prodotto_youtube_'.$i)." +++ ";
				if( $this->get('prodotto_youtube_'.$i) != null ) 
				{	
					$counter++;
					//echo "@";
				}
			} catch (Exception $e) { continue; }
		}
		
		return $counter;
	}
	
	/**
	 * Override del metodo interceptor per json
	 * 
	 * @see core/data/DBEntity::jsonInterceptor()
	 */
	public function jsonInterceptor() 
	{
		$videos = array();
		$photos = array();
		$categories = array();
		
		// Aggiunta Video al JSON
		for($i=0; $i<4; $i++)
		{
			try 
			{
				if($this->get('prodotto_youtube_'.$i) != null)
				{
					$videos[$i] = $this->get('prodotto_youtube_'.$i);
				}
			}
			catch (Exception $e) {}
		}
		
		$query = "SELECT upload_file, upload_thumb, upload_dir, u.upload_id FROM formigli_uploads_prodotti up ".
					"JOIN formigli_uploads u ON (up.upload_id=u.upload_id) WHERE prodotto_id={$this->getId()}";
		//echo $query;

		$result = $this->getDB()->query($query);

		// Aggiunta foto al JSON
		while($row = mysql_fetch_row($result))
		{
			$photo = array();
			$photo['url'] = $row[2].$row[0];
			$photo['thumb'] = $row[2].$row[1];
			$photo['upload_id'] = $row[3];
			array_push($photos, $photo);
		}
		
		// Aggiunta delle categorie al JSON
		$sql = "SELECT categoria_id FROM formigli_prodotti_categorie WHERE prodotto_id='{$this->getId()}'";
		$result = $this->getDB()->query($sql);
		while($cat = mysql_fetch_row($result))
		{
			array_push($categories, $cat[0]);
		}
		
		return ",\n\t\"videos\":".json_encode($videos).",\n\t\"photos\":".json_encode($photos).",\n\t\"categories\":".json_encode($categories);
	}
	
	// Metodi statici ///////////////////////////////////////////////////////////////////////////////////////////
	
	public static function getFromId(DB $db, $prodotto_id)
	{
		return new Prodotto($db, $prodotto_id);
	}
	
	/**
	 * Restituisce un array dei prodotti appartenenti a questa linea
	 */
	public static function listaDiTuttiIProdotti( $db )
	{
		$prodotti = array();

		$query = "SELECT * FROM formigli_prodotti";

		$result = $db->query($query);

		while($prodotto = mysql_fetch_object($result, 'Prodotto'))
		{
			array_push($prodotti, $prodotto);
		}

		return $prodotti;
	}
	
}