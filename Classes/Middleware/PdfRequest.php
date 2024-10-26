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

//use Ps14\KistPdf\Service\CacheService;
//use Ps14\KistPdf\Service\DemoService;
use Ps14\KistPdf\Service\RequestService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class PdfRequest implements MiddlewareInterface {

	public function __construct(
		private ResponseFactoryInterface $responseFactory,
		private StreamFactoryInterface $streamFactory,
		private RequestService $requestService
	) {}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$response = $handler->handle($request);

		if((int) $request->getQueryParams()['type'] !== 8080) {
			return $response;
		}

		$fileContent = $this->requestService->handle($request, $response);

	return $this->responseFactory->createResponse()
		->withBody($this->streamFactory->createStream('PDF TEST'));
// Fehler abfangen
// throw new \TYPO3\CMS\Core\Exception\SiteNotFoundException('Die Datei wurde nicht gefunden.');

		return $this->responseFactory->createResponse()
			->withHeader('Content-Type', 'application/pdf')
			->withHeader('Content-Disposition', 'attachment; filename="' . basename($fileContent['name']) . '"')
			->withHeader('Content-Transfer-Encoding', 'binary')
			->withHeader('Content-Length', (string) $fileContent['size'])
			->withHeader('Link', '<' . $fileContent['canonical'] . '>; rel="canonical"')
			->withBody($this->streamFactory->createStream($fileContent['content']));
	}
}