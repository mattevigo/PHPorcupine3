<?php
/**
 * @author Matteo Vigoni <mattevigo@gmail.com>
 * @package PHPorcupine
 * @version 2.0
 * 
 * Template per l'admin
 */
defined("_ENTRY_") or die("Restricted Access!");
defined("_ADMIN_") or header("Location:login.php");	// only admin-entry

define( "LOCAL_ROOT", $_SERVER['DOCUMENT_ROOT'] . "/templates/admin" );
define("LOCAL_WEB_ROOT", WEB_ROOT."/templates/admin" );

import("includes.contents");
import("templates.admin.includes.framework");

$db = getDB();

$user = getUser($db);	// user object for this session
$option = get_var( "option", "get", "dummy" );
//echo "Template Admin ({$user->getUsername()})<br />";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	
	
	<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT."/templates/admin/css/style.css"; ?>" />
	<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT."/templates/admin/css/custom-theme/jquery-ui-1.8.1.custom.css"; ?>" />
	<link  rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT."/templates/admin/css/fileuploader.css"; ?>" />
	<link  rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT."/templates/admin/css/style.prototypes.css"; ?>" />	
	<!-- <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT."/templates/admin/css/ui-lightness/jquery-ui-1.8.16.custom.css"; ?>" /> -->
	<link rel="stylesheet" type="text/css" href="<?php echo LOCAL_WEB_ROOT."/js/fancybox/jquery.fancybox-1.3.4.css"; ?>" media="screen" />
	<?php load_css($option); ?>
	
	<script type="text/javascript" src="<?php echo WEB_ROOT."/js/jquery-1.6.2.min.js"; ?>"></script>
	<script type="text/javascript" src="<?php echo WEB_ROOT."/js/jquery-ui-1.8.16.custom.min.js"; ?>"></script>
	<script type="text/javascript" src="<?php echo LOCAL_WEB_ROOT."/js/jquery.url.js"; ?>"></script>
	<script type="text/javascript" src="<?php echo LOCAL_WEB_ROOT."/js/ppanel.js"; ?>"></script>
	<script type="text/javascript" src="<?php echo LOCAL_WEB_ROOT."/js/ppanel.prototypes.js"; ?>"></script>
	<script type="text/javascript" src="<?php echo LOCAL_WEB_ROOT."/js/ckeditor/ckeditor.js"; ?>"></script>		
	<script type="text/javascript" src="<?php echo LOCAL_WEB_ROOT."/js/ckeditor/adapters/jquery.js"; ?>"></script>
	<script type="text/javascript" src="<?php echo LOCAL_WEB_ROOT."/js/fileuploader.js"; ?>"></script>
	<script type="text/javascript" src="<?php echo LOCAL_WEB_ROOT."/js/fancybox/jquery.mousewheel-3.0.4.pack.js"; ?>"></script>
	<script type="text/javascript" src="<?php echo LOCAL_WEB_ROOT."/js/fancybox/jquery.fancybox-1.3.4.pack.js"; ?>"></script>
	<?php load_js($option); ?>
	
	<title><?php echo SITE_NAME; ?> - Admin</title>
</head>
<body>

	<div id="header">
		<table>
			<tr>
				<td id="header-title"><b><?php echo SITE_NAME; ?></b></td>
				<td id="header-subtitle"><div id="subtitle"><?php echo SUB_TITLE; ?></div></td>
			</tr>
		
		</table>
		
		<div id="header-logout"><?php echo $user->getUsername(); ?> | <a href="logout.php">logout</a></div>
	</div>
	
	<div id="content-body">
		<div id="menu-left">
		<!--  <div id="datepicker"></div>  -->
		
			<div class="options" id="menu-left-options1">
				<h4>Preventivi</h4>
				<div id="menu-left-options-content" class="options-content">
					<div class="menu-item"><a class="menu" href="<?php echo WEB_ROOT; ?>/admin.php?option=gestione_preventivi">Gestione Preventivi</a></div>
				</div>
			</div>
		
			<div class="options" id="menu-left-options2">
				<h4>Catalogo</h4>
				<div id="menu-left-options-content" class="options-content">
					<div class="menu-item"><a class="menu" href="<?php echo WEB_ROOT; ?>/admin.php?option=anagrafica">Esplora prodotti</a></div>
					<div class="menu-item"><a class="menu" href="<?php echo WEB_ROOT; ?>/admin.php?option=anagrafica_linea">Nuova Linea</a></div>
					<div class="menu-item"><a class="menu" href="<?php echo WEB_ROOT; ?>/admin.php?option=anagrafica_prodotto">Nuovo Prodotto</a></div>
				</div>
			</div>
			
			<div class="options" id="menu-left-options3">
				<h4>Blog</h4>
				<div id="menu-left-options-content" class="options-content">
					<div class="menu-item"><a class="menu" href="<?php echo WEB_ROOT; ?>/admin.php?option=post">Nuovo Post</a></div>
					<div class="menu-item"><a class="menu" href="<?php echo WEB_ROOT; ?>/admin.php?option=post_list">Gestione Post</a></div>
				</div>
			</div>

			<div class="options" id="menu-left-options4">
				<h4>Impostazioni</h4>
				<div id="menu-left-options-content" class="options-content">
					<div class="menu-item"><a class="menu" href="<?php echo WEB_ROOT; ?>/admin.php?option=change_user">Dettagli Utente</a></div>
					<div class="menu-item"><a class="menu" href="<?php echo WEB_ROOT; ?>/admin.php?option=change_password">Cambia Password</a></div>
					<div class="menu-item"><a class="menu" href="<?php echo WEB_ROOT; ?>/admin.php?option=add_user">Aggiungi Utente</a></div>
					<div class="menu-item"><a class="menu" href="<?php echo WEB_ROOT; ?>/admin.php?option=add_user">Gestisci Utenti</a></div>
					
				</div>
			</div>
		</div>
		
		<div id="recipient">
			<?php load_option( get_var( "option", "get", "dummy" ) ); ?>
		</div> <!-- END recipient -->
		<div id="footer">PHPorcupine Administration Panel &copy;2012 <a href="mailto:mattevigo@gmail.com">Matteo Vigoni</a></div>
	</div>

</body>
</html>