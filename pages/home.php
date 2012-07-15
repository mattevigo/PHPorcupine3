<?php
/**
 * Page Home
 */
defined("_ENTRY_") or die("Restricted Access!");

import('core.site.Page');
import('core.site.Model');

import('core.formiglisrl.Linea');
import('core.formiglisrl.HomePage');

import("includes.debug");

$db = getDB();

// DB Query ////////////////////////////////////////////////////////////////////////////////////

$linee = Linea::listaDiTutteLeLinee(&$db);

// Page Controller /////////////////////////////////////////////////////////////////////////////

$page = new Page( SITE_NAME );
$page->linee = $linee;

//dumper($page);

$page->setTemplate( get_var('template', 'get', DEFAULT_TEMPLATE) );

$page->display();
?>