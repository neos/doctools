<?php
namespace TYPO3\DocTools\Domain\Model;

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
 * @todo document
 */
class ClassReference {

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var array<\TYPO3\DocTools\Domain\Model\ArgumentDefinition>
	 */
	protected $argumentDefinitions;

	/**
	 * @var array<\TYPO3\DocTools\Domain\Model\CodeExample>
	 */
	protected $codeExamples;

	/**
	 * @var string
	 */
	protected $deprecationNote;

	/**
	 * @param string $title
	 * @param string $description
	 * @param array<\TYPO3\DocTools\Domain\Model\ArgumentDefinition> $argumentDefinitions
	 * @param array<\TYPO3\DocTools\Domain\Model\CodeExample> $codeExamples
	 * @param string $deprecationNote
	 */
	public function __construct($title, $description, array $argumentDefinitions, array $codeExamples, $deprecationNote) {
		$this->title = $title;
		$this->description = $description;
		$this->argumentDefinitions = $argumentDefinitions;
		$this->codeExamples = $codeExamples;
		$this->deprecationNote = $deprecationNote;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return array
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return array<\TYPO3\DocTools\Domain\Model\ArgumentDefinition>
	 */
	public function getArgumentDefinitions() {
		return $this->argumentDefinitions;
	}

	/**
	 * @return array<\TYPO3\DocTools\Domain\Model\CodeExample>
	 */
	public function getCodeExamples() {
		return  $this->codeExamples;
	}

	/**
	 * @return string
	 */
	public function getDeprecationNote() {
		return  $this->deprecationNote;
	}
}
