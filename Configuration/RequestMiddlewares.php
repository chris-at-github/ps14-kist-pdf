<?php

return [
	'frontend' => [
		'ps14/kist-pdf/generate' => [
			'target' => \Ps14\KistPdf\Middleware\PdfRequest::class,
			'before' => [
				'typo3/cms-adminpanel/renderer',
			],
		],
	],
];