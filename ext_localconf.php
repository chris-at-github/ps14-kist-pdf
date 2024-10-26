<?php

if(defined('TYPO3') === false) {
	die('Access denied.');
}

if(isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['ps14_pdf_hash']) === false) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['ps14_pdf_hash'] = [
		'frontend' => \Ps14\KistPdf\Cache\Frontend\VariableFrontend::class,
	];
}