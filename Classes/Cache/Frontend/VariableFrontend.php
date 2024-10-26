<?php

namespace Ps14\KistPdf\Cache\Frontend;

class VariableFrontend extends \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend {

	/**
	 * Dieser Cache soll dauerhaft zur Verfuegung stehen
	 */
	public function flush() {
		// $this->backend->flush();
	}
}
