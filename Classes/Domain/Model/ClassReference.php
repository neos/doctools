<?php
namespace Documentation\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Documentation".              *
 *                                                                        *
 *                                                                        *
 */

use TYPO3\FLOW3\Annotations as FLOW3;

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
	 * @var array<\Documentation\Domain\Model\ArgumentDefinition>
	 */
	protected $argumentDefinitions;

	/**
	 * @var array<\Documentation\Domain\Model\CodeExample>
	 */
	protected $codeExamples;

	/**
	 * @var string
	 */
	protected $deprecationNote;

	/**
	 * @param string $title
	 * @param string $description
	 * @param array<\Documentation\Domain\Model\ArgumentDefinition> $argumentDefinitions
	 * @param array<\Documentation\Domain\Model\CodeExample> $codeExamples
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
	 * @return array<\Documentation\Domain\Model\ArgumentDefinition>
	 */
	public function getArgumentDefinitions() {
		return $this->argumentDefinitions;
	}

	/**
	 * @return array<\Documentation\Domain\Model\CodeExample>
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

?>