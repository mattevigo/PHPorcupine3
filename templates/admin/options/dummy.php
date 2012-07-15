<?php
/**
 * @author Matteo Vigoni <mattevigo@gmail.com>
 * @package PHPorcupine
 * @version 2.0
 * 
 * Dummy option
 */
defined("_ENTRY_") or die("Restricted Access!");
defined("_ADMIN_") or header("Location:login.php");	// only admin-entry

define("PAGE_NAME", "Dummy");

$user = get_session_user();
?>
<h4>Dummy</h4>
<div id="breadcumbs"><a href=""><?php echo SITE_NAME ?></a> &#62; <a href="">admin</a> &#62; <a href=""><?php echo PAGE_NAME; ?></a></div>

<?php if($_SESSION['message'] != null) echo "<div class=\"message\"><b>".$_SESSION['message']."</b></div>"; $_SESSION['message'] = null;?>

<div id="recipient-content">
	<h1>PHPorcupine Dummy Form</h1>
	<p>Hello Dummy!</p>

</div>