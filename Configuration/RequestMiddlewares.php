<?php

use Ps14\KistPdf\Middleware\PdfGenerate;

return [
	'frontend' => [
		'ps14/kist-pdf/generate' => [
			'target' => PdfGenerate::class,
			'before' => [
				'typo3/cms-adminpanel/renderer',
			],
		],
	],
];