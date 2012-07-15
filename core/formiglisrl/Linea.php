<?php
import("core.data.DBEntity");
import("core.formiglisrl.Prodotto");
import("core.data.Upload");

/**
 * @author Matteo Vigoni <mattevigo@gmail.com>
 * @package Formigli S.r.L.
 */
class Linea extends DBEntity
{
	const TABLE = 'formigli_sezioni';
	const PRIMARY_KEY = 'sezione_id';

	/**
	 * Costruttore
	 *
	 * @todo test con il prefix
	 */
	public function __construct(DB $db=null, $id=null)
	{
		parent::__construct($db, self::TABLE, self::PRIMARY_KEY, $id);
	}
	
	/**
	 * Ritorna l'url relativo dell'immagine di header
	 */
	public function getHeaderImage()
	{
		$ret = "";
		try {
			$header = new Upload($this->getDB(), $this->get('sezione_header_id'));
			$ret = $header->get('upload_dir').$header->get('upload_file');
		} catch (Exception $e) {}
		
		return $ret;
	}
	
	/**
	 * Ritorna un array di indirizzi relativi di foto associate alla linea.
	 *
	 * @return un array di indirizzi relativi di foto, un array vuoto se non vi sono
	 * 			foto associate
	 */
	public function getImages()
	{
		$photos = array();
		
		$query = "SELECT upload_file, upload_thumb, upload_dir, u.upload_id FROM formigli_uploads_sezioni us ".
					"JOIN formigli_uploads u ON (us.upload_id=u.upload_id) WHERE sezione_id={$this->getId()}";
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
				if( $this->get('sezione_youtube_'.$i) != null ) 
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
		$ret = "";
		$videos = array();
		$photos = array();

		// Aggiunta Video al JSON
		for($i=0; $i<4; $i++)
		{
			try
			{
				if($this->get('sezione_youtube_'.$i) != null)
				{
					$videos[$i] = $this->get('sezione_youtube_'.$i);
				}
			}
			catch (Exception $e) {}
		}

		// Aggiunta header
		$ret .= ",\n\t\"sezione_header_img\":\"".$this->getHeaderImage()."\"";

		$query = "SELECT upload_file, upload_thumb, upload_dir, u.upload_id FROM formigli_uploads_sezioni us ".
					"JOIN formigli_uploads u ON (us.upload_id=u.upload_id) WHERE sezione_id={$this->getId()}";
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

		$ret .= ",\n\t\"videos\":".json_encode($videos).",\n\t\"photos\":".json_encode($photos);

		return $ret;
	}

	/**
	 * Restituisce un array dei prodotti appartenenti a questa linea
	 */
	public function listaDeiProdotti()
	{
		$prodotti = array();

		$query = "SELECT * FROM formigli_prodotti WHERE sezione_id={$this->getId()}";

		$result = $this->getDB()->query($query);

		while($prodotto = mysql_fetch_object($result, 'Prodotto'))
		{
			array_push($prodotti, $prodotto);
		}

		return $prodotti;
	}

	// Metodi statici ////////////////////////////////////////////////////////////////////////////////////////////////////

	public static function  listaDiTutteLeLinee(DB $db)
	{
		$sql = "SELECT * FROM formigli_sezioni ORDER BY sezione_id";
		$result = $db->query($sql);

		$array = array();
		$i = 0;
		while( $linea = mysql_fetch_object($result, "Linea") )
		{
			//var_dump($obj);
			//$array[$i++] = array("sezione_id"=>$obj["sezione_id"], "sezione_nome"=>$obj["sezione_titolo"], "sezione_img_url"=>$obj["sezione_img_url"]);
			$linea->setDB(&$db);
			$array[$i++] = $linea;
		}

		//var_dump($array);
		return $array;
	}
	
	public static function lineaPerProdottoId(DB $db, $prodotto_id)
	{
		$sql = 	"SELECT DISTINCT s.* ".
				"FROM formigli_sezioni s ".
				"JOIN formigli_prodotti p ON (p.sezione_id=s.sezione_id) ".
				"WHERE p.prodotto_id=$prodotto_id";
		
		//echo $sql."<br /><br />";
		
		$result = $db->query($sql);

		$linea = mysql_fetch_object($result, "Linea");
		
		if(!$linea)
		{
			throw new EntityException("Non esiste nessuna linea associata al prodotto $prodotto_id");
		}
		
		return $linea;
	}
}