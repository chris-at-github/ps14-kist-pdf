<?php

if(defined('TYPO3') === false) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'Ps14KistPdf',
	'Pdf',
	'PDF'
);