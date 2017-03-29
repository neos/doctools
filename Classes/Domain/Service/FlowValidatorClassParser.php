<?php
namespace Neos\DocTools\Domain\Service;

/*                                                                        *
 * This script belongs to the Flow package "TYPO3.DocTools".              *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Neos\DocTools\Domain\Model\ArgumentDefinition;

/**
 * Neos.DocTools parser for Flow Validator classes.
 */
class FlowValidatorClassParser extends AbstractClassParser {

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
	 * @return array<\Neos\DocTools\Domain\Model\ArgumentDefinition>
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
	 * @return array<\Neos\DocTools\Domain\Model\CodeExample>
	 */
	protected function parseCodeExamples() {
		return array();
	}
}
