<?php

namespace Ps14\KistPdf\Service;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class CacheService {

	public const CACHE_DIRECTORY = '/cache/data/pdf/';
	public const CACHE_NAME = 'ps14_pdf_hash';

	protected FrontendInterface $cache;

	public function __construct(protected ServerRequestInterface $request, protected ResponseInterface $response) {
		$this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache(self::CACHE_NAME);
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
		return md5($this->response->getBody()->__toString());
	}

	public function has() : bool {
		$cachedResponseHash = $this->cache->get($this->getCacheKey());

		// noch kein Cache-Eintrag
		if($cachedResponseHash === false) {
			return false;
		}

		// Seiten-Hash hat sich geaendert
		if($cachedResponseHash !== $this->getResponseHash()) {
			if(is_file($this->getCachePath()) === true) {
				unlink($this->getCachePath());
			}

			return false;
		}

		// Gecachte PDF-Datei nicht mehr vorhanden
		if(is_file($this->getCachePath()) === false) {
			return false;
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