<?php
defined('TYPO3_MODE') || die();

if('BE' === TYPO3_MODE)
{
	$GLOBALS['TBE_STYLES']['skins']['alm_editor'] = array();
	$GLOBALS['TBE_STYLES']['skins']['alm_editor']['name'] = 'alm_editor';
	$GLOBALS['TBE_STYLES']['skins']['alm_editor']['stylesheetDirectories'] = array('css' => 'EXT:' . $_EXTKEY . '/Resources/Public/Backend/Css/');
}
