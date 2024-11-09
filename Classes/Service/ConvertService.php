<?php

namespace Ps14\KistPdf\Service;

use Ps14\KistPdf\Exception\ConvertException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \CloudConvert\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class ConvertService {

	const PDF_QUERY_STRING = 'pdf=1';

	public function __construct(protected ServerRequestInterface $request, protected ResponseInterface $response) {
		require_once __DIR__ . '/../../Vendor/autoload.php';
	}

	protected function getExtensionConfiguration(): array {
		return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('ps14_kist_pdf');
	}

	protected function getCaptureUrl() {
		$extensionConfiguration = $this->getExtensionConfiguration();
		$captureUrl = '';

		if(empty($extensionConfiguration['cloudconvertBaseDomain']) === false) {
			$captureUrl .= $extensionConfiguration['cloudconvertBaseDomain'];

		} else {
			$captureUrl .= $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost();
		}

		$captureUrl .= $this->request->getUri()->getPath();
		$captureUrl .= '?' . self::PDF_QUERY_STRING;

		return $captureUrl;
	}

	public function convert() : string|null {
		try {
			$extensionConfiguration = $this->getExtensionConfiguration();
			$cloudconvert = new CloudConvert(['api_key' => $extensionConfiguration['cloudconvertApiKey']]);

			$job = (new Job())
				->addTask(
					(new Task('capture-website', 'capture-kist'))
						->set('url', $this->getCaptureUrl())
						->set('output_format', 'pdf')
						->set('engine', 'chrome')
						->set('zoom', 1)
						->set('page_orientation', 'portrait')
						->set('print_background', true)
						->set('display_header_footer', false)
						->set('wait_until', 'load')
						->set('wait_time', 250)
						->set('margin_top', 10)
				)
				->addTask(
					(new Task('export/url', 'export-kist'))
						->set('input', ['capture-kist'])
						->set('inline', false)
						->set('archive_multiple_files', false)
				);

			$cloudconvert->jobs()->create($job);
			$cloudconvert->jobs()->wait($job);

			foreach($job->getExportUrls() as $file) {
				return stream_get_contents($cloudconvert->getHttpTransport()->download($file->url)->detach());
			}

			return null;

		} catch (\CloudConvert\Exceptions\Exception $e) {
			throw new ConvertException('Fehler beim Konvertieren der Webseite in PDF: ' . $e->getMessage(), $e->getCode(), $e);

		} catch (\Exception $e) {
			throw new ConvertException('Ein unerwarteter Fehler ist aufgetreten: ' . $e->getMessage(), $e->getCode(), $e);
		}
	}
}