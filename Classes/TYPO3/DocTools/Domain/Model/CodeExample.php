<?php
namespace TYPO3\DocTools\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.DocTools".             *
 *                                                                        *
 *                                                                        *
 */

use TYPO3\Flow\Annotations as Flow;

/**
 * @todo document
 */
class CodeExample {

	/**
	 * Title of the example
	 * @var string
	 */
	protected $title;

	/**
	 * Example code
	 * @var string
	 */
	protected $code;

	/**
	 * Example code format (xml, php, ...)
	 * @var string
	 */
	protected $codeFormat;

	/**
	 * Expected output
	 * @var string
	 */
	protected $output;

	/**
	 * Constructor for this code example
	 *
	 * @param string $title Title of the example
	 * @param string $code Example code
	 * @param string $codeFormat Example code format (xml, php, ...)
	 * @param string $output Expected output of the code example
	 */
	public function __construct($title, $code, $codeFormat, $output) {
		$this->title = $title;
		$this->code = $code;
		$this->codeFormat = $codeFormat;
		$this->output = $output;
	}

	/**
	 * Returns the title of this example
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns the example code
	 *
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Returns the example code format
	 *
	 * @return string
	 */
	public function getCodeFormat() {
		return $this->codeFormat;
	}

	/**
	 * Returns the expected output of this example
	 *
	 * @return string
	 */
	public function getOutput() {
		return $this->output;
	}
}
?>