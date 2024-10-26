<?php

namespace Ps14\KistPdf\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class PdfController extends ActionController {

	public function generateAction() : ResponseInterface {
		$data = [
			'status' => 'success',
			'message' => 'PDF generation started',
			'pdfUrl' => '/path/to/generated/file.pdf'
		];

		return new JsonResponse($data);
	}
}