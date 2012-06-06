<?php
namespace TYPO3\DocTools\Domain\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.DocTools".             *
 *                                                                        *
 *                                                                        *
 */

use TYPO3\FLOW3\Annotations as FLOW3;
use TYPO3\DocTools\Domain\Model\CodeExample;
use TYPO3\DocTools\Domain\Model\ArgumentDefinition;

/**
 * @todo document
 */
class Flow3ValidatorClassParser extends AbstractClassParser {

	/**
	 * @return string
	 */
	protected function parseTitle() {
		return substr($this->className, strrpos($this->className, '\\') + 1);
	}

	/**
	 * @return array
	 */
	protected function parseDescription() {
		$description = $this->classReflection->getDescription();

		$methodReflection = $this->classReflection->getMethod('isValid');
		$description .= chr(10) . chr(10) . $methodReflection->getDescription();

		$classDefaultProperties = $this->classReflection->getDefaultProperties();
		if ($classDefaultProperties['acceptsEmptyValues'] === TRUE) {
			$description .= chr(10) . chr(10) . '.. note:: A value of NULL or an empty string (\'\') is considered valid';
		}

		return $description;
	}

	/**
	 * @return array<\TYPO3\DocTools\Domain\Model\ArgumentDefinition>
	 */
	protected function parseArgumentDefinitions() {
		$options = array();
		$classDefaultProperties = $this->classReflection->getDefaultProperties();
		foreach ($classDefaultProperties['supportedOptions'] as $optionName => $optionData) {
			$options[] = new ArgumentDefinition($optionName, $optionData[2], $optionData[1], isset($optionData[3]), $optionData[1]);
		}
		return $options;
	}

	/**
	 * @return array<\TYPO3\DocTools\Domain\Model\CodeExample>
	 */
	protected function parseCodeExamples() {
		return array();
	}
}

?>