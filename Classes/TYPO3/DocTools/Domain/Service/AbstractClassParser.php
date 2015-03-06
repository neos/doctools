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
 * Abstract TYPO3.DocTools parser for classes. Extended by target specific
 * parsers to generate reference documentation.
 */
abstract class AbstractClassParser {

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var string
	 */
	protected $className;

	/**
	 * @var \TYPO3\Flow\Reflection\ClassReflection
	 */
	protected $classReflection;

	/**
	 * @param array $options
	 */
	public function __construct(array $options = array()) {
		$this->options = $options;
	}

	/**
	 * @param string $className
	 * @return \TYPO3\DocTools\Domain\Model\ClassReference
	 */
	final public function parse($className) {
		$this->className = $className;
		$this->classReflection = new \TYPO3\Flow\Reflection\ClassReflection($this->className);
		return new \TYPO3\DocTools\Domain\Model\ClassReference($this->parseTitle(), $this->parseDescription(), $this->parseArgumentDefinitions(), $this->parseCodeExamples(), $this->parseDeprecationNote());
	}

	/**
	 * @return string
	 */
	abstract protected function parseTitle();

	/**
	 * @return string
	 */
	abstract protected function parseDescription();

	/**
	 * @return array<\TYPO3\DocTools\Domain\Model\ArgumentDefinition>
	 */
	abstract protected function parseArgumentDefinitions();

	/**
	 * @return array<\TYPO3\DocTools\Domain\Model\CodeExample>
	 */
	abstract protected function parseCodeExamples();

	/**
	 * @return string
	 */
	protected function parseDeprecationNote() {
		if ($this->classReflection->isTaggedWith('deprecated')) {
			return implode(', ', $this->classReflection->getTagValues('deprecated'));
		} else {
			return '';
		}
	}
}
