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
class ArgumentDefinition {

	/**
	 * Name of the argument
	 * @var string
	 */
	protected $name;

	/**
	 * Type of the argument
	 * @var string
	 */
	protected $type;

	/**
	 * Description of the argument
	 * @var string
	 */
	protected $description;

	/**
	 * Is argument required?
	 * @var boolean
	 */
	protected $required = FALSE;

	/**
	 * Default value of the argument
	 * @var mixed
	 */
	protected $defaultValue = NULL;

	/**
	 * Constructor for this argument definition.
	 *
	 * @param string $name Name of argument
	 * @param string $type Type of argument
	 * @param string $description Description of argument
	 * @param boolean $required TRUE if argument is required
	 * @param mixed $defaultValue Default value
	 */
	public function __construct($name, $type, $description, $required, $defaultValue = NULL) {
		$this->name = $name;
		$this->type = $type;
		$this->description = $description;
		$this->required = $required;
		$this->defaultValue = $defaultValue;
	}

	/**
	 * Returns the name of the argument
	 *
	 * @return string Name of argument
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the type of the argument
	 *
	 * @return string Type of argument
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns the description of the argument
	 *
	 * @return string Description of argument
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Returns whether the argument is required or optional
	 *
	 * @return boolean TRUE if argument is optional
	 */
	public function isRequired() {
		return $this->required;
	}

	/**
	 * Returns the default value, if set
	 *
	 * @return mixed Default value
	 */
	public function getDefaultValue() {
		return $this->defaultValue;
	}
}
?>