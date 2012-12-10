<?php
namespace TYPO3\DocTools\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.DocTools".        *
 *                                                                        *
 *                                                                        *
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\DocTools\Domain\Model\CodeExample;
use TYPO3\DocTools\Domain\Model\ArgumentDefinition;

/**
 * TYPO3.DocTools parser for TYPO3 Flow TypeConverter classes.
 */
class FlowTypeConverterClassParser extends AbstractClassParser {

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

		$classDefaultProperties = $this->classReflection->getDefaultProperties();

		$description .= chr(10) . chr(10) . ':Priority: ' . $classDefaultProperties['priority']. chr(10) ;
		$description .= ':Target type: ' . $classDefaultProperties['targetType']. chr(10) ;
		if (count($classDefaultProperties['sourceTypes']) === 1) {
			$description .= ':Source type: ' . current($classDefaultProperties['sourceTypes']) . chr(10);
		} else {
			$description .= ':Source types:' . chr(10);
			$description .= ' * ' . implode(chr(10) . ' * ', $classDefaultProperties['sourceTypes']);
		}

		return $description;
	}

	/**
	 * @return array<\TYPO3\DocTools\Domain\Model\ArgumentDefinition>
	 */
	protected function parseArgumentDefinitions() {
		return array();
	}

	/**
	 * @return array<\TYPO3\DocTools\Domain\Model\CodeExample>
	 */
	protected function parseCodeExamples() {
		return array();
	}
}

?>