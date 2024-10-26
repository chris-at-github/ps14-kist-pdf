<?php

namespace Ps14\KistPdf\Service;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RequestService {

//	public function __construct(
//		private CacheService $cacheService
//	) {}

	/**
	 * @param ServerRequestInterface $request
	 * @return string
	 */
	protected function getFilename($request) {
		return 'kist-escherich-' . pathinfo($request->getUri()->getPath(),  PATHINFO_BASENAME) . '.pdf';
	}

	/**
	 * @param ServerRequestInterface $request
	 * @return string
	 */
	protected function getCanonical($request) {
		return $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . $request->getUri()->getPath();
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return array|null
	 */
	public function handle(ServerRequestInterface $request, ResponseInterface $response) : array|null {

		/** @var CacheService $cacheService */
		$cacheService = GeneralUtility::makeInstance(CacheService::class, $request, $response);

		/** @var ConvertService $convertService */
		$convertService = GeneralUtility::makeInstance(ConvertService::class, $request, $response);

		if($cacheService->has() === true) {
			$fileContent = $cacheService->get();

		} else {
			$fileContent = $convertService->convert();
			$cacheService->set($fileContent);
		}

		return [
			'content' => $fileContent,
			'size' => strlen($fileContent),
			'name' => $this->getFilename($request),
			'canonical' => $this->getCanonical($request)
		];
	}
}