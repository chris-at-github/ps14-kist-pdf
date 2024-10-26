<?php

namespace Ps14\KistPdf\Service;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use \CloudConvert\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class ConvertService {

	public function __construct(protected ServerRequestInterface $request, protected ResponseInterface $response) {
	}

	public function convert() {

		DebuggerUtility::var_dump(111);

		$cloudconvert = new CloudConvert([
			'api_key' => 'API_KEY',
			'sandbox' => false
		]);

		DebuggerUtility::var_dump($cloudconvert);

		$requestTarget = 'https://www.christian-pschorr.de/TAIFUN-CLEAN-015-KIST-ESCHERICH.pdf';
		$requestFactory = GeneralUtility::makeInstance(RequestFactory::class);

		try {
			$response = $requestFactory->request($requestTarget);

		} catch (RequestException $exception) {
			return null;
		}

		return $response->getBody()->getContents();
	}
}