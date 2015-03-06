<?php
namespace TYPO3\DocTools\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.DocTools".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * TYPO3.DocTools parser for FlowQuery Operation classes.
 */
class FlowQueryOperationClassParser extends AbstractClassParser {

	/**
	 * @return string
	 */
	protected function parseTitle() {
		return call_user_func(array($this->className, 'getShortName'));
	}

	/**
	 * @return array
	 */
	protected function parseDescription() {
		$description = $this->classReflection->getDescription();

		$methodReflection = $this->classReflection->getMethod('evaluate');
		if ($methodReflection->getDescription() !== '{@inheritdoc}') {
			$description .= chr(10) . chr(10) . $methodReflection->getDescription();
		}

		$description .= chr(10) . chr(10) . ':Implementation: ' . str_replace('\\', '\\\\', $this->className) . chr(10) ;
		$description .= ':Priority: ' . call_user_func(array($this->className, 'getPriority')) . chr(10) ;
		$description .= ':Final: ' . (call_user_func(array($this->className, 'isFinal')) ? 'Yes' : 'No') . chr(10) ;
		$description .= ':Returns: ' . implode(' ', $methodReflection->getTagValues('return')) . chr(10) ;

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
