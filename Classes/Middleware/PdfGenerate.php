<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Ps14\KistPdf\Middleware;

use Ps14\KistPdf\Service\DemoService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class PdfGenerate implements MiddlewareInterface {

	public function __construct(
		private ResponseFactoryInterface $responseFactory,
		private StreamFactoryInterface $streamFactory,
	) {}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$response = $handler->handle($request);
		$demoService = GeneralUtility::makeInstance(DemoService::class);

		if((int) $request->getQueryParams()['type'] !== 8080) {
			return $response;
		}

		$demoService->generatePageHash($response);
		$file = $demoService->requestPdf();

// Fehler abfangen
//		throw new \TYPO3\CMS\Core\Exception\SiteNotFoundException('Die Datei wurde nicht gefunden.');

		return $this->responseFactory->createResponse()
			->withHeader('Content-Type', 'application/pdf')
			->withHeader('Content-Disposition', 'attachment; filename="' . basename($file['name']) . '"')
			->withHeader('Content-Transfer-Encoding', 'binary')
			->withHeader('Content-Length', (string) $file['size'])
			->withBody($this->streamFactory->createStream($file['content']));
	}
}