<?php

namespace Ps14\KistPdf\Service;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class CacheService {

	public const CACHE_DIRECTORY = '/cache/data/pdf/';
	public const CACHE_NAME = 'ps14_pdf_hash';

	protected FrontendInterface $cache;
	private LoggerInterface $logger;

	public function __construct(protected ServerRequestInterface $request, protected ResponseInterface $response) {
		$this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache(self::CACHE_NAME);
		$this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger('Ps14KistPdf');
	}

	protected function getCacheKey() : string {
		return md5('pdf-' . $this->request->getUri()->getScheme() . $this->request->getUri()->getHost() . $this->request->getUri()->getPath());
	}

	protected function getCacheDirectory() : string {
		$cacheDirectory = \TYPO3\CMS\Core\Core\Environment::getVarPath() . self::CACHE_DIRECTORY;

		if(is_dir($cacheDirectory) === false) {
			mkdir($cacheDirectory, 0755, true);
		}

		return $cacheDirectory;
	}

	protected function getCachePath() : string {
		return $this->getCacheDirectory() . $this->getCacheKey() . '.pdf';
	}

	protected function getResponseHash() : string {
		$html = $this->response->getBody()->__toString();
		$main = '';

		if(preg_match('/<main\b[^>]*>(.*?)<\/main>/is', $html, $matches)) {
			$main = trim($matches[1]);
			$main = preg_replace('/\s+/', ' ', $main); // Optional: Whitespace normalisieren, um Unterschiede durch Formatierung zu minimieren
		}

		return md5($main);
	}

	public function has() : bool {
		$cachedResponseHash = $this->cache->get($this->getCacheKey());

		// noch kein Cache-Eintrag
		if($cachedResponseHash === false) {
			$this->logger->debug($this->getCacheKey() . ': caching request for ' . $this->request->getUri()->getScheme() . $this->request->getUri()->getHost() . $this->request->getUri()->getPath());
			$this->logger->debug($this->getCacheKey() . ': no cached response hash');
//			return false;
		}

		// Seiten-Hash hat sich geaendert
		if($cachedResponseHash !== $this->getResponseHash()) {
			$this->logger->debug($this->getCacheKey() . ': caching request for ' . $this->request->getUri()->getScheme() . $this->request->getUri()->getHost() . $this->request->getUri()->getPath());
			$this->logger->debug($this->getCacheKey() . ': cached response hash: "' . $cachedResponseHash . '" is different to current response hash ' . $this->getResponseHash());
//			if(is_file($this->getCachePath()) === true) {
//				unlink($this->getCachePath());
//			}
//
//			return false;
		}

		// Gecachte PDF-Datei nicht mehr vorhanden
		if(is_file($this->getCachePath()) === false) {
			$this->logger->debug($this->getCacheKey() . ': caching request for ' . $this->request->getUri()->getScheme() . $this->request->getUri()->getHost() . $this->request->getUri()->getPath());
			$this->logger->debug($this->getCacheKey() . ': no cached file found for ' . $this->getCachePath());

			return false;
		}

		// gecachte PDF Datei ist kleiner als 100kb
		if(filesize($this->getCachePath()) < 100 * 1024) { // 100 KB = 102400 bytes
			$this->logger->debug($this->getCacheKey() . ': caching request for ' . $this->request->getUri()->getScheme() . $this->request->getUri()->getHost() . $this->request->getUri()->getPath());
			$this->logger->debug($this->getCacheKey() . ': cached file "' . $this->getCachePath() . '" is smaller than 100kb');
		}

		return true;
	}

	public function get() : string|null {
		if(is_file($this->getCachePath()) === true) {
			return file_get_contents($this->getCachePath());
		}

		return null;
	}

	public function set($fileContent) : void {

		// Datei abgespeichern
		file_put_contents($this->getCachePath(), $fileContent);

		// Cache Flag setzen
		$this->cache->set($this->getCacheKey(), $this->getResponseHash(), [], 63072000);
	}
}