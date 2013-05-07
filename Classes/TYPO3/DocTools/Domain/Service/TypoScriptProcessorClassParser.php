<?php
namespace TYPO3\DocTools\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.DocTools".        *
 *                                                                        *
 *                                                                        *
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\DocTools\Domain\Model\ArgumentDefinition;

/**
 * TYPO3.DocTools parser for TypoScript Processor classes.
 */
class TypoScriptProcessorClassParser extends AbstractClassParser {

	/**
	 * @return string
	 */
	protected function parseTitle() {
		return lcfirst(substr($this->className, strrpos($this->className, '\\') + 1, -9));
	}

	/**
	 * @return array
	 */
	protected function parseDescription() {
		$description = $this->classReflection->getDescription();

		$description .= chr(10) . chr(10) . 'Implementated in: ' . str_replace('\\', '\\\\', $this->className) . chr(10) ;

		return $description;
	}

	/**
	 * @return array<\TYPO3\DocTools\Domain\Model\ArgumentDefinition>
	 */
	protected function parseArgumentDefinitions() {
		$methods = $this->classReflection->getMethods(\ReflectionMethod::IS_PUBLIC);
		$classDefaultProperties = $this->classReflection->getDefaultProperties();

		/** @var $methodReflection \TYPO3\Flow\Reflection\MethodReflection */
		/** @var $parameterReflection \TYPO3\Flow\Reflection\ParameterReflection */
		$options = array();
		foreach ($methods as $methodReflection) {
			if (substr($methodReflection->getName(), 0, 3) !== 'set') {
				continue;
			}

			$name = lcfirst(substr($methodReflection->getName(), 3));
			$parameterReflection = current($methodReflection->getParameters());
			$paramData = explode(' ', current($methodReflection->getTagValues('param')));

			$options[] = new ArgumentDefinition(
				$name,
				$paramData[0],
				$methodReflection->getDescription(),
				!$parameterReflection->isOptional(),
				isset($classDefaultProperties[$name]) ? $classDefaultProperties[$name] : NULL
			);
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