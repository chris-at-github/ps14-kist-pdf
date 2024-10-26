<?php

namespace Ps14\KistPdf\Service;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class DemoService {

	/**
	 * @var string
	 */
	protected $pageHash = '';

	public function generatePageHash(ResponseInterface $response) {
		$this->pageHash = md5($response->getBody()->getContents());
	}

	protected function getCacheDirectory() {
		$cacheDirectory = \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/cache/data/pdf/';

		if(is_dir($cacheDirectory) === false) {
			mkdir($cacheDirectory, 0755, true);
		}

		return $cacheDirectory;
	}

	protected function putCache($key, $content) {
		$cachePath = $this->getCacheDirectory() . $key . '.pdf';

		file_put_contents($cachePath, $content);
	}

	public function requestPdf() {
		$requestTarget = 'https://www.christian-pschorr.de/TAIFUN-CLEAN-015-KIST-ESCHERICH.pdf';
		$requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
		$pageHash = $this->pageHash;

		try {
			$response = $requestFactory->request($requestTarget);

		} catch (RequestException $exception) {
			return false;
		}

		$pdfContent = $response->getBody()->getContents();

		$this->putCache($pageHash, $pdfContent);

		return [
			'content' => $pdfContent,
			'size' => strlen($pdfContent),
			'name' => 'taifun-clean-015-kist-escherich.pdf',
		];
	}
}