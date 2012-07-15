<?php
// PHPorcupine script style
defined("_ENTRY_") or die("Restricted Access!");
defined("_ADMIN_") or header("Location:login.php");	// only admin-entry

import("core.util.qqUploader");
import("core.data.Upload");

// Istanza di Upload per DB
$upload = new Upload( getDB(), null );

$type = get_var("type", "get", null);
$uploads_dir = "uploads/";

if( $type == null )
{
	echo htmlspecialchars(json_encode(array('success' => false, 'error' => "Type not specified")), ENT_NOQUOTES);
	die();
}
else if( strcasecmp($type, "linea") == 0 )
{
	$uploads_dir .= "linee/";
	$object = "linea";
}
else if( strcasecmp($type, "prodotto") == 0 )
{
	$uploads_dir .= "prodotti/";
	$object = "prodotto";
}
else if( strcasecmp($type, "header") == 0 )
{
	$uploads_dir .= "linee/";
	$object = "header";
}

$upload->set('upload_dir', $uploads_dir);
$upload->set('upload_type', "IMAGE");

// list of valid extensions, ex. array("jpeg", "xml", "bmp")
$allowedExtensions = array("jpeg", "gif", "jpg", "png");
// max file size in bytes
$sizeLimit = 10 * 1024 * 1024;

$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
$result = $uploader->handleUpload($uploads_dir, true);

if( isset($result['success']) && $result['success'] == true )
{
	$upload->set( 'upload_file', $result['filename'] );
	$upload->set('upload_thumb', $uploader->thumbName);
	$upload->set('upload_date', date( 'Y-m-d H:i:s' ) );
	$upload->store();
	
	$result['upload_id'] = $upload->getId();
}

// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
