<?php
namespace TYPO3\DocTools\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.DocTools".        *
 *                                                                        *
 *                                                                        *
 */

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

?>