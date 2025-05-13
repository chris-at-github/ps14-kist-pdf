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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PdfRequest implements MiddlewareInterface {

	public function __construct(
		private ResponseFactoryInterface $responseFactory,
		private StreamFactoryInterface $streamFactory,
		private RequestService $requestService
	) {}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$response = $handler->handle($request);
		$params = $request->getQueryParams();

		if(isset($params['type']) === false || (int) $params['type'] !== 8080) {
			return $response;
		}

		if($this->isPageEnabled($request) === false) {
			return new RedirectResponse($request->getUri()->getPath(), 307);
		}

		$fileContent = $this->requestService->handle($request, $response);

//		return $this->responseFactory->createResponse()
//			->withBody($this->streamFactory->createStream('PDF TEST'));

		return $this->responseFactory->createResponse()
			->withHeader('Content-Type', 'application/pdf')
			->withHeader('Content-Disposition', 'attachment; filename="' . basename($fileContent['name']) . '"')
			->withHeader('Content-Transfer-Encoding', 'binary')
			->withHeader('Content-Length', (string) $fileContent['size'])
			->withHeader('Link', '<' . $fileContent['canonical'] . '>; rel="canonical"')
			->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
			->withHeader('Pragma', 'no-cache')
			->withHeader('Expires', '0')
			->withBody($this->streamFactory->createStream($fileContent['content']));
	}

	public function isPageEnabled(ServerRequestInterface $request) : bool {
		$pageArguments = $request->getAttribute('routing');

		if(($pageArguments instanceof PageArguments) === false) {
			return false;
		}

		// Seitendatensatz aus DB laden (Doctrine)
		$connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
		$page = $connection->select(
			['tx_site_disable_sticky_pdf'],
			'pages',
			['uid' => (int) $pageArguments->getPageId()]
		)->fetchAssociative();

		if($page === false) {
			return false;
		}

		if((int) $page['tx_site_disable_sticky_pdf'] === 1) {
			return false;
		}

		return true;
	}
}